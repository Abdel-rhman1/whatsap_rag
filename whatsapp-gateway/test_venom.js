const venom = require('venom-bot');
const express = require('express');
const app = express();
app.use(express.json());

console.log('Starting Venom with your config...');

venom
  .create(
    'whatsapp-session',
    (base64Qr) => {
        console.log('QR RECEIVED!');
        // We'll just log that we got it
    },
    (status) => {
        console.log('Status:', status);
    },
    {
      headless: true,
      useChrome: true,
      executablePath: '/usr/bin/google-chrome-stable',
      browserArgs: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    }
  )
  .then((client) => {
    console.log('Venom Ready!');
    app.get('/test', (req, res) => res.send('OK'));
    app.listen(3001, () => {
      console.log('Test Server started on port 3001');
    });
  })
  .catch((error) => {
    console.error('Venom init error:', error);
  });
