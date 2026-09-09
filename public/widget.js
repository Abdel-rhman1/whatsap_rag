/**
 * RAGHub AI - Universal Embeddable Chat Widget
 * CDN Script for Client Websites
 */
(function () {
    // Prevent duplicate injection
    if (window.__RAG_HUB_WIDGET_LOADED__) return;
    window.__RAG_HUB_WIDGET_LOADED__ = true;

    const currentScript = document.currentScript || (function () {
        const scripts = document.getElementsByTagName('script');
        for (let i = scripts.length - 1; i >= 0; i--) {
            if (scripts[i].src && scripts[i].src.includes('widget.js')) return scripts[i];
        }
        return scripts[scripts.length - 1];
    })();

    const apiKey = currentScript.getAttribute('data-api-key') || currentScript.getAttribute('data-key') || window.RAG_HUB_API_KEY;
    if (!apiKey) {
        console.warn('[RAGHub Widget] Missing data-api-key or data-key attribute on script tag.');
        return;
    }

    // Determine Base API URL
    let baseUrl = currentScript.src ? currentScript.src.replace(/\/widget\.js(\?.*)?$/i, '') : '';
    if (!baseUrl || baseUrl.startsWith('blob:') || baseUrl.startsWith('data:')) {
        baseUrl = window.location.origin;
    }
    const configUrl = `${baseUrl}/api/widget/config?key=${encodeURIComponent(apiKey)}`;
    const chatUrl = `${baseUrl}/api/widget/chat`;

    // Session Management
    const storagePrefix = `rag_chat_${apiKey.substring(0, 10)}_`;
    let sessionId = localStorage.getItem(storagePrefix + 'session_id');
    if (!sessionId) {
        sessionId = 'w_' + Math.random().toString(36).substring(2, 11) + '_' + Date.now().toString(36);
        localStorage.setItem(storagePrefix + 'session_id', sessionId);
    }

    let config = {
        bot_name: 'AI Support',
        bubble_title: 'Chat with us',
        theme: 'dark',
        primary_color: '#6366f1',
        greeting_message: 'Hello! 👋 How can I help you today?',
        placeholder_text: 'Type a message...',
        position: 'bottom-right',
        suggested_questions: ['What services do you offer?', 'How can I get started?'],
        is_enabled: true,
        sound_enabled: true
    };

    let isOpen = false;
    let isSending = false;
    let soundMuted = false;

    // Load saved messages from LocalStorage
    function getStoredMessages() {
        try {
            const raw = localStorage.getItem(storagePrefix + 'history');
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveStoredMessages(messages) {
        try {
            // Keep last 40 messages
            const sliced = messages.slice(-40);
            localStorage.setItem(storagePrefix + 'history', JSON.stringify(sliced));
        } catch (e) { }
    }

    // Play subtle soft notification chime using Web Audio API (no external asset needed)
    function playChime() {
        if (soundMuted || !config.sound_enabled) return;
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const now = ctx.currentTime;
            
            // Note 1
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now); // D5
            gain1.gain.setValueAtTime(0.08, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.3);

            // Note 2
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.1); // A5
            gain2.gain.setValueAtTime(0.09, now + 0.1);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.1);
            osc2.stop(now + 0.45);
        } catch (e) { }
    }

    // Simple markdown formatting helper
    function formatMessageText(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Links
        escaped = escaped.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline; font-weight: 600;">$1</a>');
        // Newlines
        escaped = escaped.replace(/\n/g, '<br>');
        return escaped;
    }

    // Format local time
    function getNowTime() {
        const d = new Date();
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Inject Widget Styles & Container
    function injectUI() {
        const root = document.createElement('div');
        root.id = 'rag-hub-widget-root';
        root.setAttribute('data-theme', config.theme);
        
        const isLeft = config.position === 'bottom-left';
        const primaryColor = config.primary_color || '#6366f1';
        const isDark = config.theme === 'dark';

        // Scoped stylesheet
        const style = document.createElement('style');
        style.id = 'rag-hub-widget-styles';
        style.innerHTML = `
            #rag-hub-widget-root {
                --rag-primary: ${primaryColor};
                --rag-primary-dark: #4f46e5;
                --rag-bg: ${isDark ? '#0f172a' : '#ffffff'};
                --rag-card: ${isDark ? '#1e293b' : '#f8fafc'};
                --rag-text: ${isDark ? '#f8fafc' : '#0f172a'};
                --rag-text-muted: ${isDark ? '#94a3b8' : '#64748b'};
                --rag-border: ${isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)'};
                --rag-bot-msg: ${isDark ? '#1e293b' : '#f1f5f9'};
                --rag-bot-text: ${isDark ? '#f8fafc' : '#1e293b'};
                position: fixed;
                bottom: 24px;
                ${isLeft ? 'left: 24px;' : 'right: 24px;'}
                z-index: 2147483640;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                color-scheme: ${isDark ? 'dark' : 'light'};
            }

            #rag-hub-widget-root * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            /* Launcher Button */
            .rag-launcher {
                width: 62px;
                height: 62px;
                border-radius: 50%;
                background: var(--rag-primary);
                box-shadow: 0 8px 24px rgba(0,0,0,0.22), 0 2px 6px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: none;
                outline: none;
                transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
                position: relative;
            }

            .rag-launcher:hover {
                transform: scale(1.08);
                box-shadow: 0 12px 30px rgba(0,0,0,0.3);
            }

            .rag-launcher svg {
                width: 28px;
                height: 28px;
                fill: white;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }

            .rag-launcher .icon-chat {
                position: absolute;
            }
            .rag-launcher .icon-close {
                position: absolute;
                opacity: 0;
                transform: rotate(-90deg) scale(0.6);
            }

            .rag-launcher.open .icon-chat {
                opacity: 0;
                transform: rotate(90deg) scale(0.6);
            }
            .rag-launcher.open .icon-close {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }

            /* Tooltip Pill */
            .rag-bubble-tip {
                position: absolute;
                bottom: 12px;
                ${isLeft ? 'left: 74px;' : 'right: 74px;'}
                background: var(--rag-card);
                color: var(--rag-text);
                padding: 10px 16px;
                border-radius: 24px;
                box-shadow: 0 6px 20px rgba(0,0,0,0.18);
                font-size: 13px;
                font-weight: 600;
                white-space: nowrap;
                border: 1px solid var(--rag-border);
                cursor: pointer;
                transition: opacity 0.3s, transform 0.3s;
                display: flex;
                align-items: center;
                gap: 8px;
                pointer-events: auto;
            }

            .rag-bubble-tip:hover {
                transform: translateY(-2px);
            }

            /* Chat Window Panel */
            .rag-window {
                position: absolute;
                bottom: 78px;
                ${isLeft ? 'left: 0;' : 'right: 0;'}
                width: 380px;
                height: 590px;
                max-height: calc(100vh - 110px);
                max-width: calc(100vw - 40px);
                background: var(--rag-bg);
                border-radius: 20px;
                box-shadow: 0 16px 48px rgba(0,0,0,0.25), 0 0 0 1px var(--rag-border);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                opacity: 0;
                pointer-events: none;
                transform: translateY(20px) scale(0.95);
                transform-origin: bottom ${isLeft ? 'left' : 'right'};
                transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .rag-window.open {
                opacity: 1;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }

            /* Header */
            .rag-header {
                background: var(--rag-primary);
                color: #ffffff;
                padding: 16px 18px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                shrink: 0;
            }

            .rag-header-info {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .rag-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: rgba(255,255,255,0.22);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 16px;
                border: 2px solid rgba(255,255,255,0.3);
                position: relative;
            }

            .rag-avatar-dot {
                position: absolute;
                bottom: 0;
                right: 0;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #10b981;
                border: 2px solid #ffffff;
            }

            .rag-header-text h4 {
                font-size: 15px;
                font-weight: 700;
                letter-spacing: -0.2px;
                color: #ffffff;
            }

            .rag-header-text p {
                font-size: 11px;
                opacity: 0.9;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .rag-header-actions {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .rag-header-btn {
                background: rgba(255,255,255,0.15);
                border: none;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }

            .rag-header-btn:hover {
                background: rgba(255,255,255,0.28);
            }

            /* Messages Area */
            .rag-messages-area {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                display: flex;
                flex-direction: column;
                gap: 12px;
                background: ${isDark ? '#0b1324' : '#f8fafc'};
                scroll-behavior: smooth;
            }

            .rag-messages-area::-webkit-scrollbar {
                width: 5px;
            }
            .rag-messages-area::-webkit-scrollbar-thumb {
                background: var(--rag-border);
                border-radius: 10px;
            }

            /* Bubbles */
            .rag-msg-row {
                display: flex;
                flex-direction: column;
                max-width: 82%;
            }

            .rag-msg-row.user {
                align-self: flex-end;
                align-items: flex-end;
            }

            .rag-msg-row.bot {
                align-self: flex-start;
                align-items: flex-start;
            }

            .rag-bubble {
                padding: 10px 14px;
                border-radius: 16px;
                font-size: 13.5px;
                line-height: 1.5;
                word-break: break-word;
                box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            }

            .rag-msg-row.user .rag-bubble {
                background: var(--rag-primary);
                color: #ffffff;
                border-bottom-right-radius: 4px;
            }

            .rag-msg-row.bot .rag-bubble {
                background: var(--rag-card);
                color: var(--rag-text);
                border-bottom-left-radius: 4px;
                border: 1px solid var(--rag-border);
            }

            .rag-msg-time {
                font-size: 10px;
                color: var(--rag-text-muted);
                margin-top: 4px;
                padding: 0 4px;
            }

            /* Starter Questions */
            .rag-starter-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .rag-chip {
                background: var(--rag-card);
                color: var(--rag-primary);
                border: 1px solid var(--rag-border);
                padding: 8px 12px;
                border-radius: 16px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s;
                text-align: left;
            }

            .rag-chip:hover {
                background: var(--rag-primary);
                color: #ffffff;
                transform: translateY(-1px);
            }

            /* Typing Indicator */
            .rag-typing {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 10px 16px;
                background: var(--rag-card);
                border: 1px solid var(--rag-border);
                border-radius: 16px;
                border-bottom-left-radius: 4px;
                align-self: flex-start;
                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            }

            .rag-typing-dot {
                width: 6px;
                height: 6px;
                background: var(--rag-text-muted);
                border-radius: 50%;
                animation: ragBlink 1.4s infinite both;
            }
            .rag-typing-dot:nth-child(2) { animation-delay: 0.2s; }
            .rag-typing-dot:nth-child(3) { animation-delay: 0.4s; }

            @keyframes ragBlink {
                0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
                40% { transform: scale(1); opacity: 1; }
            }

            /* Input Area */
            .rag-footer {
                padding: 12px 16px;
                background: var(--rag-card);
                border-top: 1px solid var(--rag-border);
                display: flex;
                align-items: center;
                gap: 10px;
                position: relative;
            }

            .rag-input-box {
                flex: 1;
                background: var(--rag-bg);
                border: 1px solid var(--rag-border);
                border-radius: 24px;
                padding: 9px 16px;
                font-size: 13.5px;
                color: var(--rag-text);
                outline: none;
                transition: border-color 0.2s;
            }

            .rag-input-box:focus {
                border-color: var(--rag-primary);
            }

            .rag-send-btn {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                background: var(--rag-primary);
                border: none;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: transform 0.15s, opacity 0.2s;
                shrink: 0;
            }

            .rag-send-btn:disabled {
                opacity: 0.4;
                cursor: not-allowed;
            }

            .rag-send-btn:not(:disabled):hover {
                transform: scale(1.05);
            }

            .rag-powered {
                text-align: center;
                font-size: 10px;
                color: var(--rag-text-muted);
                padding: 4px;
                background: var(--rag-card);
                border-top: 1px solid var(--rag-border);
            }

            /* Mobile Friendly */
            @media (max-width: 480px) {
                #rag-hub-widget-root {
                    bottom: 16px;
                    right: 16px;
                    left: 16px;
                }
                .rag-window {
                    bottom: 74px;
                    left: 0;
                    right: 0;
                    width: 100%;
                    height: calc(100vh - 100px);
                    max-height: none;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(style);

        // Build HTML
        root.innerHTML = `
            <!-- Tip Banner -->
            <div class="rag-bubble-tip" id="rag-bubble-tip">
                <span>💬</span>
                <span>${config.bubble_title || 'Chat with us'}</span>
            </div>

            <!-- Launcher -->
            <button class="rag-launcher" id="rag-launcher" aria-label="Open Chat">
                <svg class="icon-chat" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                <svg class="icon-close" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>

            <!-- Chat Window -->
            <div class="rag-window" id="rag-window">
                <!-- Header -->
                <div class="rag-header">
                    <div class="rag-header-info">
                        <div class="rag-avatar">
                            <span>🤖</span>
                            <span class="rag-avatar-dot"></span>
                        </div>
                        <div class="rag-header-text">
                            <h4 id="rag-header-bot-name">${config.bot_name || 'AI Assistant'}</h4>
                            <p><span>●</span> Active Now</p>
                        </div>
                    </div>
                    <div class="rag-header-actions">
                        <button class="rag-header-btn" id="rag-sound-btn" title="Toggle Sound">
                            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/></svg>
                        </button>
                        <button class="rag-header-btn" id="rag-clear-btn" title="Clear Chat">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        <button class="rag-header-btn" id="rag-close-btn" title="Close">
                            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="rag-messages-area" id="rag-messages-area">
                    <!-- Dynamic Messages -->
                </div>

                <!-- Footer -->
                <div class="rag-footer">
                    <input type="text" class="rag-input-box" id="rag-input" placeholder="${config.placeholder_text || 'Type a message...'}" autocomplete="off" />
                    <button class="rag-send-btn" id="rag-send-btn" disabled>
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>

                <div class="rag-powered">
                    ⚡ Powered by <strong>RagHub AI</strong>
                </div>
            </div>
        `;

        document.body.appendChild(root);

        // Bind Elements
        const launcher = document.getElementById('rag-launcher');
        const tip = document.getElementById('rag-bubble-tip');
        const win = document.getElementById('rag-window');
        const closeBtn = document.getElementById('rag-close-btn');
        const clearBtn = document.getElementById('rag-clear-btn');
        const soundBtn = document.getElementById('rag-sound-btn');
        const input = document.getElementById('rag-input');
        const sendBtn = document.getElementById('rag-send-btn');
        const messagesArea = document.getElementById('rag-messages-area');

        // Toggle Open/Close
        function toggleChat() {
            isOpen = !isOpen;
            if (isOpen) {
                launcher.classList.add('open');
                win.classList.add('open');
                if (tip) tip.style.display = 'none';
                setTimeout(() => input.focus(), 150);
                scrollBottom();
            } else {
                launcher.classList.remove('open');
                win.classList.remove('open');
            }
        }

        launcher.onclick = toggleChat;
        if (tip) tip.onclick = toggleChat;
        closeBtn.onclick = toggleChat;

        // Clear Chat
        clearBtn.onclick = function () {
            if (confirm('Clear chat history?')) {
                localStorage.removeItem(storagePrefix + 'history');
                renderMessages();
            }
        };

        // Sound Toggle
        soundBtn.onclick = function () {
            soundMuted = !soundMuted;
            soundBtn.style.opacity = soundMuted ? '0.4' : '1';
        };

        // Input validation & send button toggle
        input.oninput = function () {
            sendBtn.disabled = !input.value.trim() || isSending;
        };

        input.onkeypress = function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSend();
            }
        };

        sendBtn.onclick = handleSend;

        // Render Initial Messages
        renderMessages();
    }

    function scrollBottom() {
        const area = document.getElementById('rag-messages-area');
        if (area) {
            area.scrollTop = area.scrollHeight;
        }
    }

    function renderMessages() {
        const area = document.getElementById('rag-messages-area');
        if (!area) return;

        area.innerHTML = '';

        // Add greeting message
        const greetingRow = document.createElement('div');
        greetingRow.className = 'rag-msg-row bot';
        greetingRow.innerHTML = `
            <div class="rag-bubble">${formatMessageText(config.greeting_message || 'Hello! How can I help you today?')}</div>
            <span class="rag-msg-time">${getNowTime()}</span>
        `;
        area.appendChild(greetingRow);

        // Stored messages
        const stored = getStoredMessages();
        stored.forEach(msg => {
            appendBubble(msg.text, msg.role, msg.time, false);
        });

        // Add suggested questions chips if available
        if (stored.length === 0 && config.suggested_questions && config.suggested_questions.length > 0) {
            const chipsBox = document.createElement('div');
            chipsBox.className = 'rag-starter-chips';
            config.suggested_questions.forEach(q => {
                const chip = document.createElement('button');
                chip.className = 'rag-chip';
                chip.innerText = q;
                chip.onclick = function () {
                    chipsBox.remove();
                    sendMessageText(q);
                };
                chipsBox.appendChild(chip);
            });
            area.appendChild(chipsBox);
        }

        scrollBottom();
    }

    function appendBubble(text, role, time, persist = true) {
        const area = document.getElementById('rag-messages-area');
        if (!area) return;

        const row = document.createElement('div');
        row.className = `rag-msg-row ${role}`;
        row.innerHTML = `
            <div class="rag-bubble">${formatMessageText(text)}</div>
            <span class="rag-msg-time">${time || getNowTime()}</span>
        `;
        area.appendChild(row);
        scrollBottom();

        if (persist) {
            const list = getStoredMessages();
            list.push({ text, role, time: time || getNowTime() });
            saveStoredMessages(list);
        }
    }

    function showTypingIndicator() {
        const area = document.getElementById('rag-messages-area');
        if (!area) return;
        const typing = document.createElement('div');
        typing.className = 'rag-typing';
        typing.id = 'rag-active-typing';
        typing.innerHTML = `
            <span class="rag-typing-dot"></span>
            <span class="rag-typing-dot"></span>
            <span class="rag-typing-dot"></span>
        `;
        area.appendChild(typing);
        scrollBottom();
    }

    function removeTypingIndicator() {
        const el = document.getElementById('rag-active-typing');
        if (el) el.remove();
    }

    function handleSend() {
        const input = document.getElementById('rag-input');
        if (!input) return;
        const text = input.value.trim();
        if (!text || isSending) return;

        input.value = '';
        const sendBtn = document.getElementById('rag-send-btn');
        if (sendBtn) sendBtn.disabled = true;

        sendMessageText(text);
    }

    async function sendMessageText(text) {
        if (isSending) return;
        isSending = true;

        // Remove any starter chips
        const chips = document.querySelector('.rag-starter-chips');
        if (chips) chips.remove();

        // Append user bubble
        appendBubble(text, 'user', getNowTime(), true);
        showTypingIndicator();

        try {
            const res = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': apiKey
                },
                body: JSON.stringify({
                    message: text,
                    session_id: sessionId
                })
            });

            const data = await res.json();
            removeTypingIndicator();

            if (data.reply) {
                appendBubble(data.reply, 'bot', getNowTime(), true);
                playChime();
            } else if (data.error) {
                appendBubble(data.message || 'Sorry, something went wrong.', 'bot', getNowTime(), true);
            }
        } catch (err) {
            console.error('[RAGHub Widget] Chat error:', err);
            removeTypingIndicator();
            appendBubble('Could not reach the server. Please check your internet connection.', 'bot', getNowTime(), true);
        } finally {
            isSending = false;
            const sendBtn = document.getElementById('rag-send-btn');
            const input = document.getElementById('rag-input');
            if (input && sendBtn) {
                sendBtn.disabled = !input.value.trim();
            }
        }
    }

    // Initialize Widget: Fetch Remote Configuration
    async function initWidget() {
        try {
            const res = await fetch(configUrl);
            if (res.ok) {
                const remote = await res.json();
                config = Object.assign(config, remote);
            }
        } catch (e) {
            console.warn('[RAGHub Widget] Could not fetch remote config, using defaults.');
        }

        // Only mount if enabled
        if (config.is_enabled) {
            injectUI();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else {
        initWidget();
    }
})();
