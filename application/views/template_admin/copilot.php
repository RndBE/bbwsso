<!-- Copilot Chatbot Button & Modal -->
<style>
    /* ── Floating Action Button ── */
    #copilot-fab {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #303481, #4a4fc4);
        color: #fff;
        border: none;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(48, 52, 129, .45);
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        font-family: Inter, system-ui, sans-serif;
        transition: transform .2s, box-shadow .2s;
    }

    #copilot-fab:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 28px rgba(48, 52, 129, .6);
    }

    #copilot-fab svg {
        flex-shrink: 0;
    }

    /* ── Modal Overlay ── */
    #copilot-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(0, 0, 0, .5);
        backdrop-filter: blur(4px);
        justify-content: center;
        align-items: center;
    }

    #copilot-overlay.active {
        display: flex;
    }

    /* ── Modal Container ── */
    #copilot-modal {
        width: 95%;
        max-width: 760px;
        height: 85vh;
        max-height: 700px;
        background: #212121;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 12px 48px rgba(0, 0, 0, .4);
        animation: copilotSlideUp .3s ease;
    }

    @keyframes copilotSlideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ── Header ── */
    #copilot-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #333;
    }

    #copilot-header h3 {
        margin: 0;
        color: #fff;
        font-size: 17px;
        font-weight: 600;
        font-family: Inter, system-ui, sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #copilot-header h3 .dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
    }

    #copilot-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: background .15s, color .15s;
    }

    #copilot-close:hover {
        background: #333;
        color: #fff;
    }

    /* ── Chat Body ── */
    #copilot-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        scrollbar-width: thin;
        scrollbar-color: #444 transparent;
    }

    /* Welcome Screen */
    #copilot-welcome {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #9ca3af;
    }

    #copilot-welcome .icon-wrapper {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #303481, #4a4fc4);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }

    #copilot-welcome h4 {
        color: #e5e7eb;
        font-size: 20px;
        margin: 0 0 8px;
        font-family: Inter, system-ui, sans-serif;
    }

    #copilot-welcome p {
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
        max-width: 360px;
    }

    /* Chat Bubbles */
    .copilot-msg {
        max-width: 85%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.6;
        font-family: Inter, system-ui, sans-serif;
        word-wrap: break-word;
    }

    .copilot-msg.user {
        align-self: flex-end;
        background: #303481;
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .copilot-msg.bot {
        align-self: flex-start;
        background: #2f2f2f;
        color: #e5e7eb;
        border-bottom-left-radius: 4px;
    }

    /* ── Input Area ── */
    #copilot-input-area {
        padding: 12px 16px 16px;
        border-top: 1px solid #333;
        background: #212121;
    }

    #copilot-input-wrap {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        background: #2f2f2f;
        border: 1px solid #444;
        border-radius: 12px;
        padding: 8px 12px;
        transition: border-color .2s;
    }

    #copilot-input-wrap:focus-within {
        border-color: #4a4fc4;
    }

    #copilot-input {
        flex: 1;
        background: none;
        border: none;
        outline: none;
        color: #e5e7eb;
        font-size: 14px;
        font-family: Inter, system-ui, sans-serif;
        resize: none;
        max-height: 120px;
        line-height: 1.5;
        padding: 4px 0;
    }

    #copilot-input::placeholder {
        color: #6b7280;
    }

    #copilot-send {
        background: #303481;
        border: none;
        color: #fff;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background .15s;
    }

    #copilot-send:hover {
        background: #4a4fc4;
    }

    #copilot-send:disabled {
        background: #444;
        cursor: default;
    }

    /* ── Typing Indicator ── */
    .copilot-typing {
        display: flex;
        gap: 4px;
        padding: 12px 16px;
        align-self: flex-start;
    }

    .copilot-typing span {
        width: 8px;
        height: 8px;
        background: #6b7280;
        border-radius: 50%;
        animation: copilotBounce .6s infinite alternate;
    }

    .copilot-typing span:nth-child(2) {
        animation-delay: .2s;
    }

    .copilot-typing span:nth-child(3) {
        animation-delay: .4s;
    }

    @keyframes copilotBounce {
        to {
            transform: translateY(-6px);
            opacity: .4;
        }
    }

    /* ── Responsive ── */
    @media (max-width: 576px) {
        #copilot-fab span.fab-label {
            display: none;
        }

        #copilot-fab {
            padding: 14px;
            border-radius: 50%;
        }

        #copilot-modal {
            width: 100%;
            height: 100vh;
            max-height: none;
            border-radius: 0;
        }
    }
</style>

<!-- FAB Button -->
<button id="copilot-fab" onclick="copilotOpen()">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
        <path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2" />
        <path
            d="M19.5 13a2.5 2.5 0 0 0 2.5 2.5a2.5 2.5 0 0 0 -2.5 2.5a2.5 2.5 0 0 0 -2.5 -2.5a2.5 2.5 0 0 0 2.5 -2.5" />
    </svg>
    <span class="fab-label">Copilot</span>
</button>

<!-- Modal Overlay -->
<div id="copilot-overlay" onclick="copilotOverlayClick(event)">
    <div id="copilot-modal">
        <!-- Header -->
        <div id="copilot-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                    <path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2" />
                </svg>
                Copilot
                <span class="dot"></span>
            </h3>
            <button id="copilot-close" onclick="copilotClose()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6l-12 12" />
                    <path d="M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Chat Body -->
        <div id="copilot-body">
            <div id="copilot-welcome">
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                        <path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2" />
                        <path
                            d="M19.5 13a2.5 2.5 0 0 0 2.5 2.5a2.5 2.5 0 0 0 -2.5 2.5a2.5 2.5 0 0 0 -2.5 -2.5a2.5 2.5 0 0 0 2.5 -2.5" />
                    </svg>
                </div>
                <h4>Halo! Saya Copilot 👋</h4>
                <p>Asisten AI Anda untuk membantu memahami data monitoring. Tanyakan apa saja tentang data logger, curah
                    hujan, tinggi muka air, dan lainnya.</p>
            </div>
        </div>

        <!-- Input Area -->
        <div id="copilot-input-area">
            <div id="copilot-input-wrap">
                <textarea id="copilot-input" rows="1" placeholder="Ketik pesan Anda..."
                    onkeydown="copilotKeyDown(event)" oninput="copilotAutoResize(this)"></textarea>
                <button id="copilot-send" onclick="copilotSendMessage()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        // ── DOM refs ──
        const overlay = document.getElementById('copilot-overlay');
        const body = document.getElementById('copilot-body');
        const input = document.getElementById('copilot-input');
        const sendBtn = document.getElementById('copilot-send');
        const welcome = document.getElementById('copilot-welcome');

        // Dummy responses
        const RESPONSES = [
            "Terima kasih atas pertanyaannya! Saat ini fitur Copilot masih dalam tahap pengembangan. Kami akan segera mengaktifkan kemampuan AI untuk membantu analisis data Anda.",
            "Fitur ini akan segera tersedia! Copilot akan mampu membantu Anda menganalisis data curah hujan, tinggi muka air, dan parameter monitoring lainnya.",
            "Copilot sedang dikembangkan untuk memberikan insight terbaik dari data monitoring Anda. Nantikan pembaruan selanjutnya!",
            "Terima kasih! Saat ini saya belum bisa memproses data secara langsung, namun fitur ini sedang dalam pengembangan aktif.",
        ];
        let respIdx = 0;

        // ── Open / Close ──
        window.copilotOpen = function () {
            overlay.classList.add('active');
            setTimeout(function () { input.focus(); }, 300);
        };
        window.copilotClose = function () {
            overlay.classList.remove('active');
        };
        window.copilotOverlayClick = function (e) {
            if (e.target === overlay) copilotClose();
        };

        // ── Auto-resize textarea ──
        window.copilotAutoResize = function (el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        };

        // ── Send message ──
        window.copilotSendMessage = function () {
            var text = input.value.trim();
            if (!text) return;

            // Hide welcome
            if (welcome) welcome.style.display = 'none';

            // Add user message
            addMsg(text, 'user');
            input.value = '';
            input.style.height = 'auto';

            // Show typing indicator
            var typing = document.createElement('div');
            typing.className = 'copilot-typing';
            typing.innerHTML = '<span></span><span></span><span></span>';
            body.appendChild(typing);
            scrollBottom();

            // Simulate bot reply
            setTimeout(function () {
                typing.remove();
                var reply = RESPONSES[respIdx % RESPONSES.length];
                respIdx++;
                addMsg(reply, 'bot');
            }, 1200 + Math.random() * 800);
        };

        // ── Keydown handler ──
        window.copilotKeyDown = function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                copilotSendMessage();
            }
        };

        // ── Helpers ──
        function addMsg(text, type) {
            var div = document.createElement('div');
            div.className = 'copilot-msg ' + type;
            div.textContent = text;
            body.appendChild(div);
            scrollBottom();
        }
        function scrollBottom() {
            setTimeout(function () { body.scrollTop = body.scrollHeight; }, 50);
        }
    })();
</script>