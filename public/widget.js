(function() {
    const script = document.currentScript;
    const widgetKey = script.getAttribute('data-key');
    const baseUrl = script.src.split('/widget.js')[0];
    const apiUrl = `${baseUrl}/api/widget/${widgetKey}`;

    let config = {};
    let sessionId = localStorage.getItem('rag_hub_session') || Math.random().toString(36).substring(7);
    localStorage.setItem('rag_hub_session', sessionId);

    // Create Widget Container
    const container = document.createElement('div');
    container.id = 'rag-hub-widget';
    document.body.appendChild(container);

    const style = document.createElement('style');
    style.innerHTML = `
        #rag-hub-widget { position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: sans-serif; }
        .rag-button { width: 60px; height: 60px; border-radius: 50%; display: flex; items-center; justify-content: center; cursor: pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transition: all 0.3s; }
        .rag-button:hover { transform: scale(1.1); }
        .rag-panel { position: absolute; bottom: 80px; right: 0; width: 380px; height: 600px; background: white; border-radius: 20px; display: none; flex-direction: column; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid #eee; overflow: hidden; }
        .rag-header { padding: 20px; color: white; display: flex; items-center; gap: 10px; font-weight: bold; }
        .rag-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; background: #f9f9f9; }
        .rag-msg { padding: 10px 15px; border-radius: 15px; max-width: 80%; font-size: 14px; line-height: 1.5; }
        .rag-msg-user { align-self: flex-end; background: #eee; color: #333; border-bottom-right-radius: 2px; }
        .rag-msg-bot { align-self: flex-start; background: white; border-bottom-left-radius: 2px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .rag-input-area { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .rag-input { flex: 1; border: 1px solid #ddd; padding: 10px 15px; border-radius: 20px; outline: none; }
        .rag-send { border: none; background: none; color: #6366f1; cursor: pointer; font-weight: bold; }
        .rag-panel.open { display: flex; }
    `;
    document.head.appendChild(style);

    container.innerHTML = `
        <div class="rag-panel" id="rag-panel">
            <div class="rag-header" id="rag-header">
                <span id="rag-name">Loading...</span>
            </div>
            <div class="rag-messages" id="rag-messages"></div>
            <div class="rag-input-area">
                <input type="text" class="rag-input" id="rag-input" placeholder="Ask something...">
                <button class="rag-send" id="rag-send">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></button>
            </div>
        </div>
        <div class="rag-button" id="rag-button">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
    `;

    const panel = document.getElementById('rag-panel');
    const button = document.getElementById('rag-button');
    const header = document.getElementById('rag-header');
    const nameLabel = document.getElementById('rag-name');
    const messages = document.getElementById('rag-messages');
    const input = document.getElementById('rag-input');
    const sendBtn = document.getElementById('rag-send');

    button.onclick = () => panel.classList.toggle('open');

    async function init() {
        const res = await fetch(`${apiUrl}/config`);
        config = await res.json();
        
        nameLabel.innerText = config.name;
        header.style.background = config.primary_color;
        button.style.background = config.primary_color;
        sendBtn.style.color = config.primary_color;

        if (config.greeting) {
            addMessage(config.greeting, 'bot');
        }
    }

    function addMessage(text, role) {
        const div = document.createElement('div');
        div.className = `rag-msg rag-msg-${role}`;
        div.innerText = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        input.value = '';
        addMessage(text, 'user');

        const res = await fetch(`${apiUrl}/chat`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, session_id: sessionId })
        });
        const data = await res.json();
        addMessage(data.answer, 'bot');
    }

    sendBtn.onclick = sendMessage;
    input.onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

    init();
})();
