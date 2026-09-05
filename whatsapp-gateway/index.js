/**
 * WhatsApp Gateway - Robust Baileys Migration
 * Supports Multi-device, persistent sessions, auto-reconnect, and RAG integration.
 */

// Fix for Node v18+ environments
if (!global.crypto) {
    try {
        global.crypto = require('crypto').webcrypto;
    } catch (e) {
        global.crypto = require('crypto');
    }
}

const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers,
    downloadMediaMessage
} = require('baileys');
const express = require('express');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');
const pino = require('pino');
const axios = require('axios');
const crypto = require('crypto');

// Load environment variables
require('dotenv').config({ path: path.join(__dirname, '../.env') });

const app = express();
app.use(express.json());

// Configuration
const AUTH_DIR = path.join(__dirname, 'auth');
const MEDIA_DIR = path.join(__dirname, 'media'); // Temporary media storage
const LOG_FILE = path.join(__dirname, 'gateway.log');
const PORT = process.env.WHATSAPP_GATEWAY_PORT || 3000;

if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}
if (!fs.existsSync(MEDIA_DIR)) {
    fs.mkdirSync(MEDIA_DIR, { recursive: true });
}

// Serve media files statically
app.use('/media', express.static(MEDIA_DIR));

// Memory storage for active sessions
const sessions = {};
const qrCodes = {};
const sessionStatus = {}; // 'INITIALIZING', 'QR_READY', 'AUTHENTICATING', 'LINKING', 'CONNECTED', 'FAILED'
const sessionPhones = {};

// Logger configuration
const logger = pino({ level: 'silent' }); // Silent pino to keep console clean for our custom logs

function log(id, msg) {
    const timestamp = new Date().toISOString();
    const line = `[${timestamp}] [${id || 'SYSTEM'}] ${msg}`;
    fs.appendFileSync(LOG_FILE, line + '\n');
    console.log(line);
}

/**
 * Core Baileys Session Logic
 */
async function startSession(id, force = false) {
    // Cleanup existing session if forced
    if (sessions[id] && force) {
        log(id, 'Forcing session cleanup for restart...');
        try {
            sessions[id].ev.removeAllListeners();
            sessions[id].end(new Error('RESTART_REQUESTED'));
        } catch (e) { }
        delete sessions[id];
    }

    // Prevent double initialization
    if (sessions[id] && sessionStatus[id] !== 'FAILED') return;
    if (sessionStatus[id] === 'INITIALIZING' || sessionStatus[id] === 'LINKING') {
        if (!force) return;
    }

    log(id, force ? 'Force-restarting Baileys session...' : 'Initializing Baileys session...');
    sessionStatus[id] = 'INITIALIZING';

    try {
        const { state, saveCreds } = await useMultiFileAuthState(path.join(AUTH_DIR, id));

        // Get latest version to avoid "xml-not-well-formed" errors due to outdated protocol
        let version;
        try {
            const result = await fetchLatestBaileysVersion();
            version = result.version;
            log(id, `Using Baileys version: ${version.join('.')}`);
        } catch (e) {
            log(id, 'Failed to fetch latest version, using fallback.');
            version = [2, 3000, 1015901307];
        }

        const sock = makeWASocket({
            version,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger),
            },
            printQRInTerminal: false,
            logger,
            browser: Browsers.ubuntu('Chrome'),
            syncFullHistory: false,
            markOnlineOnConnect: true,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 30000, // Keep connection alive
        });

        sessions[id] = sock;

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                log(id, 'New QR code generated. Waiting for scan...');
                qrCodes[id] = qr;
                sessionStatus[id] = 'QR_READY';
            }

            if (connection === 'close') {
                const error = lastDisconnect?.error;
                const statusCode = error?.output?.statusCode || error?.code;
                const reason = error?.message || 'Unknown reason';

                const isLoggedOut = statusCode === DisconnectReason.loggedOut;
                const isStreamError = reason.includes('xml-not-well-formed') || reason.includes('Stream Errored');

                log(id, `Connection closed. Status: ${statusCode}. Reason: ${reason}`);

                // Clean up session reference
                delete sessions[id];
                delete qrCodes[id];

                if (isLoggedOut) {
                    log(id, 'Logged out permanently. Deleting session data.');
                    sessionStatus[id] = 'FAILED';
                    fs.rmSync(path.join(AUTH_DIR, id), { recursive: true, force: true });
                } else {
                    // Handle stream errors (xml-not-well-formed) or normal drops
                    sessionStatus[id] = 'FAILED';
                    const delay = (isStreamError || statusCode === 515) ? 2000 : 5000;
                    log(id, `Auto-reconnecting in ${delay}ms...`);
                    setTimeout(() => startSession(id), delay);
                }
            }

            else if (connection === 'open') {
                const user = sock.user || state.creds.me;
                const phone = user?.id ? user.id.split(':')[0].split('@')[0] : 'unknown';

                log(id, `SUCCESS: Connected! Account: ${phone}`);
                sessionStatus[id] = 'CONNECTED';
                sessionPhones[id] = phone;
                delete qrCodes[id];
            }

            else if (connection === 'connecting') {
                // Determine if we are linking (scanning QR) or just reconnecting
                if (qrCodes[id]) {
                    sessionStatus[id] = 'LINKING';
                } else if (sessionStatus[id] !== 'CONNECTED') {
                    sessionStatus[id] = 'AUTHENTICATING';
                }
            }
        });

        // Save credentials as they update
        sock.ev.on('creds.update', saveCreds);

        // Handle Incoming Messages
        sock.ev.on('messages.upsert', async ({ messages, type }) => {
            if (type !== 'notify') return;

            for (const msg of messages) {
                // Skip if message has no content or is from self
                if (!msg.message || msg.key.fromMe) continue;

                const from = msg.key.remoteJid;
                const pushName = msg.pushName || 'Guest';

                // Enhanced message detection
                const messageType = Object.keys(msg.message)[0];
                let body = '';
                let typeOfMsg = 'text';
                let mediaUrl = null;
                let fileName = null;
                let mimeType = null;

                if (msg.message.conversation) {
                    body = msg.message.conversation;
                } else if (msg.message.extendedTextMessage) {
                    body = msg.message.extendedTextMessage.text;
                } else if (msg.message.buttonsResponseMessage) {
                    body = msg.message.buttonsResponseMessage.selectedButtonId;
                } else if (msg.message.audioMessage) {
                    typeOfMsg = 'audio';
                    mimeType = msg.message.audioMessage.mimetype;
                    try {
                        log(id, 'Downloading audio message...');
                        const buffer = await downloadMediaMessage(msg, 'buffer', {}, { logger });
                        const name = `audio_${Date.now()}.ogg`;
                        fs.writeFileSync(path.join(MEDIA_DIR, name), buffer);
                        const publicUrl = process.env.WHATSAPP_GATEWAY_INTERNAL_URL || `http://localhost:${PORT}`;
                        mediaUrl = `${publicUrl}/media/${name}`;
                        body = '[Audio Message]';
                    } catch (e) {
                        log(id, `Audio download failed: ${e.message}`);
                    }
                } else if (msg.message.documentMessage) {
                    typeOfMsg = 'document';
                    fileName = msg.message.documentMessage.fileName;
                    mimeType = msg.message.documentMessage.mimetype;
                    try {
                        const buffer = await downloadMediaMessage(msg, 'buffer', {}, { logger });
                        const name = `doc_${Date.now()}_${fileName}`;
                        fs.writeFileSync(path.join(MEDIA_DIR, name), buffer);
                        const publicUrl = process.env.WHATSAPP_GATEWAY_INTERNAL_URL || `http://localhost:${PORT}`;
                        mediaUrl = `${publicUrl}/media/${name}`;
                        body = `[Document: ${fileName}]`;
                    } catch (e) {
                        log(id, `Doc download failed: ${e.message}`);
                    }
                }

                if (!body && !mediaUrl) continue;

                log(id, `Received ${typeOfMsg} from ${from} (${pushName}): ${body.substring(0, 50)}...`);

                // Forward to Laravel Webhook with enhanced payload
                forwardToWebhook(id, from, pushName, body, typeOfMsg, mediaUrl, mimeType, fileName);
            }
        });

    } catch (err) {
        log(id, `CRITICAL ERROR: ${err.message}`);
        sessionStatus[id] = 'FAILED';
        setTimeout(() => startSession(id), 10000);
    }
}

/**
 * Forward message to Laravel Webhook
 */
async function forwardToWebhook(instanceId, from, pushName, body, type = 'text', mediaUrl = null, mimeType = null, fileName = null) {
    const webhookUrl = process.env.WHATSAPP_WEBHOOK_URL || `${process.env.APP_URL}/api/whatsapp/webhook`;
    const secret = process.env.WHATSAPP_SECRET;

    if (!webhookUrl || !secret) {
        log(instanceId, 'Webhook configuration missing. Skipping forward.');
        return;
    }

    const payload = JSON.stringify({
        instance_id: instanceId,
        from: from,
        body: body,
        pushName: pushName,
        type: type,
        media_url: mediaUrl,
        mime_type: mimeType,
        file_name: fileName,
        timestamp: Date.now()
    });

    const signature = crypto.createHmac('sha256', secret).update(payload).digest('hex');

    try {
        await axios.post(webhookUrl, payload, {
            headers: {
                'Content-Type': 'application/json',
                'X-WHATSAPP-SIGNATURE': signature
            },
            timeout: 10000 // 10s timeout for webhook
        });
    } catch (e) {
        const errorMsg = e.response?.data?.message || e.message;
        log(instanceId, `Webhook delivery failed: ${errorMsg}`);
    }
}

/**
 * HTTP Endpoints
 */

// QR Code Delivery
app.get('/qr/:id', async (req, res) => {
    const id = req.params.id;

    if (sessionStatus[id] === 'CONNECTED') return res.status(200).send('CONNECTED');

    if (qrCodes[id]) {
        try {
            const buffer = await QRCode.toBuffer(qrCodes[id]);
            res.writeHead(200, { 'Content-Type': 'image/png', 'Cache-Control': 'no-cache' });
            return res.end(buffer);
        } catch (e) {
            return res.status(500).send('QR_ERROR');
        }
    }

    // Auto-init if not tracking
    if (!sessionStatus[id] || sessionStatus[id] === 'FAILED') startSession(id);
    res.status(202).send('LOADING');
});

// Detailed Session Status
app.get('/status/:id', (req, res) => {
    const id = req.params.id;
    if (!sessionStatus[id] || sessionStatus[id] === 'FAILED') startSession(id);

    res.json({
        id,
        status: sessionStatus[id] || 'INITIALIZING',
        hasQr: !!qrCodes[id],
        phone: sessionPhones[id] || null
    });
});

// API: Send Message
app.post('/send', async (req, res) => {
    const { instance_id, to, message } = req.body;

    if (!instance_id || !to || !message) {
        return res.status(400).json({ error: 'Missing required parameters (instance_id, to, message)' });
    }

    const sock = sessions[instance_id];
    if (!sock || sessionStatus[instance_id] !== 'CONNECTED') {
        return res.status(400).json({ error: 'WhatsApp session not connected' });
    }

    try {
        const jid = to.includes('@') ? to : `${to}@s.whatsapp.net`;
        log(instance_id, `Attempting to send message to JID: ${jid}`);

        // Use a timeout for the actual Baileys call to prevent long-hanging requests
        const sendPromise = sock.sendMessage(jid, { text: message });

        // Race against a 30s safety timeout
        const result = await Promise.race([
            sendPromise,
            new Promise((_, reject) => setTimeout(() => reject(new Error('Internal Gateway Timeout')), 30000))
        ]);

        res.json({ success: true, message: 'Sent', data: result });
    } catch (e) {
        log(instance_id, `Send error: ${e.message}`);

        // If it's a timeout or connection issue, monitor for refresh
        if (e.message.includes('Timed Out') || e.message.includes('Timeout')) {
            // Logic to potentially restart session if it hangs repeatedly
            log(instance_id, 'Detected send timeout, verifying connection health...');
            const readyState = sock.ws?.readyState;
            if (readyState !== 1) { // 1 = OPEN
                log(instance_id, `Socket state is ${readyState}, triggering forced reconnect.`);
                startSession(instance_id, true);
            } else {
                // Even if OPEN, if it timed out, it might be a ghost connection
                log(instance_id, 'Socket is OPEN but timed out. Potential ghost connection, forcing restart.');
                startSession(instance_id, true);
            }
        }

        res.status(500).json({ error: e.message });
    }
});

// API: Force Start Session
app.post('/start', (req, res) => {
    const { instance_id } = req.body;
    if (!instance_id) return res.status(400).json({ error: 'instance_id required' });
    startSession(instance_id);
    res.json({ success: true, status: 'INITIALIZING' });
});

// Delete Session
app.delete('/session/:id', async (req, res) => {
    const id = req.params.id;
    log(id, 'Manual session deletion requested.');

    if (sessions[id]) {
        try {
            sessions[id].ev.removeAllListeners();
            await sessions[id].logout();
        } catch (e) { /* ignore logout errors */ }
    }

    delete sessions[id];
    delete qrCodes[id];
    delete sessionStatus[id];
    delete sessionPhones[id];

    const authPath = path.join(AUTH_DIR, id);
    if (fs.existsSync(authPath)) {
        fs.rmSync(authPath, { recursive: true, force: true });
    }

    res.json({ success: true, message: 'Session purged' });
});

// Dashboard View
app.get('/dashboard/:id', (req, res) => {
    const id = req.params.id;
    const dashPath = path.join(__dirname, 'test-dashboard.html');
    if (fs.existsSync(dashPath)) {
        let html = fs.readFileSync(dashPath, 'utf8');
        res.send(html.replace(/sessionId = '.*'/g, `sessionId = '${id}'`));
    } else {
        res.status(404).send('Dashboard template not found');
    }
});

// Server initialization
app.listen(PORT, '0.0.0.0', () => {
    log(null, `WhatsApp Gateway active on port ${PORT}`);

    // Auto-resume existing sessions on startup
    if (fs.existsSync(AUTH_DIR)) {
        const sessionsOnDisk = fs.readdirSync(AUTH_DIR).filter(f => fs.statSync(path.join(AUTH_DIR, f)).isDirectory());
        sessionsOnDisk.forEach(id => {
            log(id, 'Resume session from storage.');
            startSession(id).catch(e => log(id, `Startup error: ${e.message}`));
        });
    }
});