/* Chatbox AI — logika front-end (tanpa library) */
(function () {
    'use strict';

    const cfg = window.CHAT_CONFIG;
    const $ = (id) => document.getElementById(id);

    const thread = $('thread');
    const input  = $('input');
    const sendBtn = $('btn-send');

    let history = [];   // riwayat percakapan yang dikirim ke server
    let busy = false;

    /* ---------- Util ---------- */
    function escapeHtml(s) {
        return s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // Markdown ringan: **tebal**, daftar "- ", baris baru
    function format(text) {
        const lines = escapeHtml(text).split('\n');
        let html = '', inList = false;
        for (const raw of lines) {
            const line = raw.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            const m = line.match(/^\s*(?:[-*]|\d+\.)\s+(.*)$/);
            if (m) {
                if (!inList) { html += '<ul>'; inList = true; }
                html += '<li>' + m[1] + '</li>';
            } else {
                if (inList) { html += '</ul>'; inList = false; }
                html += line.trim() === '' ? '<br>' : line + '<br>';
            }
        }
        if (inList) html += '</ul>';
        return html.replace(/(<br>)+$/, '');
    }

    function scrollThread() { thread.scrollTop = thread.scrollHeight; }

    function addMessage(role, text, opts = {}) {
        const el = document.createElement('div');
        el.className = 'msg ' + role + (opts.error ? ' error' : '');
        if (role === 'bot') el.innerHTML = format(text);
        else el.textContent = text;
        thread.appendChild(el);
        scrollThread();
        return el;
    }

    function addTyping() {
        const el = document.createElement('div');
        el.className = 'msg bot';
        el.setAttribute('aria-label', cfg.loadingText);
        el.innerHTML = '<span class="typing"><i></i><i></i><i></i></span>';
        thread.appendChild(el);
        scrollThread();
        return el;
    }

    function autoGrow() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 160) + 'px';
    }

    function setBusy(state) {
        busy = state;
        sendBtn.disabled = state;
    }

    /* ---------- Kirim pesan ---------- */
    async function send(text) {
        text = (text || '').trim();
        if (!text || busy) return;

        addMessage('user', text);
        history.push({ role: 'user', content: text });
        input.value = '';
        autoGrow();
        setBusy(true);

        const typing = addTyping();

        try {
            const res = await fetch(cfg.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ messages: history })
            });
            const data = await res.json().catch(() => ({}));
            typing.remove();

            if (!res.ok || !data.reply) {
                history.pop();
                addMessage('bot', data.error || cfg.errorText, { error: true });
            } else {
                history.push({ role: 'assistant', content: data.reply });
                addMessage('bot', data.reply);
            }
        } catch (err) {
            typing.remove();
            history.pop();
            addMessage('bot', cfg.errorText, { error: true });
        } finally {
            setBusy(false);
            input.focus();
        }
    }

    function reset() {
        history = [];
        thread.innerHTML = '';
        addMessage('bot', cfg.greeting);
        input.value = '';
        autoGrow();
        input.focus();
    }

    /* ---------- Event ---------- */
    sendBtn.addEventListener('click', () => send(input.value));

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send(input.value);
        }
    });
    input.addEventListener('input', autoGrow);

    $('btn-new').addEventListener('click', reset);
    $('btn-visi').addEventListener('click', () => {
        $('visi-misi').scrollIntoView({ behavior: 'smooth' });
    });

    document.querySelectorAll('.chip').forEach((chip) => {
        chip.addEventListener('click', () => send(chip.dataset.q));
    });

    /* ---------- Mulai ---------- */
    addMessage('bot', cfg.greeting);
})();
