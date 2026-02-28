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

    /* Suggestion Chips */
    .copilot-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-top: 16px;
    }

    .copilot-chip {
        background: #2f2f2f;
        border: 1px solid #444;
        color: #d1d5db;
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        font-family: Inter, system-ui, sans-serif;
        transition: background .15s, border-color .15s, color .15s;
    }

    .copilot-chip:hover {
        background: #3a3a3a;
        border-color: #4a4fc4;
        color: #fff;
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

    /* Markdown inside bot messages */
    .copilot-msg.bot strong {
        color: #fff;
    }

    .copilot-msg.bot em {
        color: #c4b5fd;
    }

    .copilot-msg.bot code {
        background: #1a1a1a;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 13px;
        color: #a5f3fc;
    }

    .copilot-msg.bot ul,
    .copilot-msg.bot ol {
        margin: 6px 0;
        padding-left: 20px;
    }

    .copilot-msg.bot li {
        margin-bottom: 4px;
    }

    .copilot-msg.bot p {
        margin: 0 0 8px;
    }

    .copilot-msg.bot p:last-child {
        margin-bottom: 0;
    }

    .copilot-msg.bot table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
        font-size: 13px;
    }

    .copilot-msg.bot th,
    .copilot-msg.bot td {
        border: 1px solid #444;
        padding: 6px 10px;
        text-align: left;
    }

    .copilot-msg.bot th {
        background: #1a1a1a;
        color: #a5b4fc;
        font-weight: 600;
    }

    /* Error message style */
    .copilot-msg.bot.error {
        background: #3b1f1f;
        border-left: 3px solid #ef4444;
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

    /* ── Mic Button ── */
    #copilot-mic {
        background: none;
        border: none;
        color: #9ca3af;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background .15s, color .15s;
    }

    #copilot-mic:hover {
        background: #333;
        color: #fff;
    }

    #copilot-mic.recording {
        background: #dc2626;
        color: #fff;
        animation: copilotPulse 1s infinite;
    }

    @keyframes copilotPulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.5);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
        }
    }

    /* ── Recording Wave Animation ── */
    #copilot-wave {
        display: none;
        flex: 1;
        align-items: center;
        justify-content: center;
        gap: 3px;
        height: 36px;
        padding: 0 8px;
    }

    #copilot-wave.active {
        display: flex;
    }

    #copilot-wave .wave-bar {
        width: 3px;
        height: 6px;
        border-radius: 2px;
        background: linear-gradient(180deg, #ef4444, #f97316);
        animation: copilotWave 0.8s ease-in-out infinite;
    }

    #copilot-wave .wave-bar:nth-child(1) { animation-delay: 0s; }
    #copilot-wave .wave-bar:nth-child(2) { animation-delay: 0.1s; }
    #copilot-wave .wave-bar:nth-child(3) { animation-delay: 0.2s; }
    #copilot-wave .wave-bar:nth-child(4) { animation-delay: 0.3s; }
    #copilot-wave .wave-bar:nth-child(5) { animation-delay: 0.4s; }
    #copilot-wave .wave-bar:nth-child(6) { animation-delay: 0.3s; }
    #copilot-wave .wave-bar:nth-child(7) { animation-delay: 0.2s; }
    #copilot-wave .wave-bar:nth-child(8) { animation-delay: 0.1s; }
    #copilot-wave .wave-bar:nth-child(9) { animation-delay: 0s; }
    #copilot-wave .wave-bar:nth-child(10) { animation-delay: 0.15s; }
    #copilot-wave .wave-bar:nth-child(11) { animation-delay: 0.25s; }
    #copilot-wave .wave-bar:nth-child(12) { animation-delay: 0.35s; }
    #copilot-wave .wave-bar:nth-child(13) { animation-delay: 0.2s; }
    #copilot-wave .wave-bar:nth-child(14) { animation-delay: 0.1s; }
    #copilot-wave .wave-bar:nth-child(15) { animation-delay: 0.05s; }

    @keyframes copilotWave {
        0%, 100% { height: 6px; opacity: 0.4; }
        50% { height: 24px; opacity: 1; }
    }

    #copilot-wave .wave-label {
        color: #ef4444;
        font-size: 12px;
        font-weight: 600;
        font-family: Inter, system-ui, sans-serif;
        margin-left: 10px;
        white-space: nowrap;
        animation: copilotBlink 1s infinite;
    }

    @keyframes copilotBlink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
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
                <div class="copilot-suggestions">
                    <button class="copilot-chip"
                        onclick="copilotQuickSend('Berapa total pos monitoring yang aktif?')">📊 Total pos
                        aktif</button>
                    <button class="copilot-chip" onclick="copilotQuickSend('Tampilkan daftar pos curah hujan')">🌧️ Pos
                        curah hujan</button>
                    <button class="copilot-chip" onclick="copilotQuickSend('Cek status semua logger')">🔌 Status
                        koneksi</button>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div id="copilot-input-area">
            <div id="copilot-input-wrap">
                <textarea id="copilot-input" rows="1" placeholder="Ketik pesan Anda..."
                    onkeydown="copilotKeyDown(event)" oninput="copilotAutoResize(this)"></textarea>
                <div id="copilot-wave">
                    <span class="wave-bar"></span><span class="wave-bar"></span><span class="wave-bar"></span>
                    <span class="wave-bar"></span><span class="wave-bar"></span><span class="wave-bar"></span>
                    <span class="wave-bar"></span><span class="wave-bar"></span><span class="wave-bar"></span>
                    <span class="wave-bar"></span><span class="wave-bar"></span><span class="wave-bar"></span>
                    <span class="wave-bar"></span><span class="wave-bar"></span><span class="wave-bar"></span>
                    <span class="wave-label">Merekam...</span>
                </div>
                <button id="copilot-mic" onclick="copilotToggleMic()" title="Tahan untuk bicara">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a3 3 0 0 0 -3 3v7a3 3 0 0 0 6 0v-7a3 3 0 0 0 -3 -3z" />
                        <path d="M19 10v2a7 7 0 0 1 -14 0v-2" />
                        <line x1="12" y1="19" x2="12" y2="22" />
                    </svg>
                </button>
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
        // ── Config ──
        var API_URL = '<?= base_url() ?>chatbot/chat';
        var TRANSCRIBE_URL = '<?= base_url() ?>chatbot/transcribe';

        // ── DOM refs ──
        var overlay = document.getElementById('copilot-overlay');
        var body = document.getElementById('copilot-body');
        var input = document.getElementById('copilot-input');
        var sendBtn = document.getElementById('copilot-send');
        var welcome = document.getElementById('copilot-welcome');
        var micBtn = document.getElementById('copilot-mic');
        var waveEl = document.getElementById('copilot-wave');

        // ── Session-based context (backend manages full history) ──
        var sessionId = null;
        var isSending = false;
        var mediaRecorder = null;
        var isRecording = false;
        var audioChunks = [];

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

        // ── New Chat (reset session) ──
        window.copilotNewChat = function () {
            sessionId = null;
            // Clear chat body
            while (body.firstChild) body.removeChild(body.firstChild);
            // Re-add welcome
            body.innerHTML = document.getElementById('copilot-welcome-tpl').innerHTML;
            welcome = document.getElementById('copilot-welcome');
        };

        // ── Auto-resize textarea ──
        window.copilotAutoResize = function (el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        };

        // ── Quick suggestion send ──
        window.copilotQuickSend = function (text) {
            input.value = text;
            copilotSendMessage();
        };

        // ── Voice Input (Whisper STT) ──
        window.copilotToggleMic = function () {
            if (isRecording) {
                // Stop recording
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                }
                return;
            }

            // Request microphone permission
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Browser Anda tidak mendukung perekaman suara.');
                return;
            }

            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function (stream) {
                    isRecording = true;
                    audioChunks = [];
                    micBtn.classList.add('recording');

                    // Show wave, hide textarea
                    input.style.display = 'none';
                    waveEl.classList.add('active');
                    sendBtn.style.display = 'none';

                    mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });

                    mediaRecorder.ondataavailable = function (e) {
                        if (e.data.size > 0) audioChunks.push(e.data);
                    };

                    mediaRecorder.onstop = function () {
                        isRecording = false;
                        micBtn.classList.remove('recording');

                        // Hide wave, show textarea + send
                        waveEl.classList.remove('active');
                        input.style.display = '';
                        sendBtn.style.display = '';

                        // Stop all tracks
                        stream.getTracks().forEach(function (t) { t.stop(); });

                        if (audioChunks.length === 0) return;

                        var audioBlob = new Blob(audioChunks, { type: 'audio/webm' });

                        // Show transcribing state
                        input.placeholder = '⏳ Mentranskrip suara...';
                        micBtn.disabled = true;

                        // Send to Whisper
                        var formData = new FormData();
                        formData.append('audio', audioBlob, 'recording.webm');

                        fetch(TRANSCRIBE_URL, {
                            method: 'POST',
                            body: formData
                        })
                            .then(function (res) { return res.json(); })
                            .then(function (data) {
                                if (data.status === 'sukses' && data.text) {
                                    input.value = data.text;
                                    input.placeholder = 'Ketik pesan Anda...';
                                    // Auto-send the transcribed text
                                    copilotSendMessage();
                                } else {
                                    input.placeholder = 'Ketik pesan Anda...';
                                    alert('Gagal mentranskripsi: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(function (err) {
                                input.placeholder = 'Ketik pesan Anda...';
                                console.error('Transcribe error:', err);
                                alert('Gagal menghubungi server transkripsi.');
                            })
                            .finally(function () {
                                micBtn.disabled = false;
                            });
                    };

                    mediaRecorder.start();
                })
                .catch(function (err) {
                    console.error('Mic permission denied:', err);
                    alert('Izin mikrofon ditolak. Silakan izinkan akses mikrofon di browser.');
                });
        };

        // ── Send message ──
        window.copilotSendMessage = function () {
            var text = input.value.trim();
            if (!text || isSending) return;

            isSending = true;
            sendBtn.disabled = true;

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

            // Build request body
            var reqBody = { message: text };
            if (sessionId) {
                reqBody.session_id = sessionId;
            }

            // Call API
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(reqBody)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    typing.remove();

                    // Save session_id from backend
                    if (data.session_id) {
                        sessionId = data.session_id;
                    }

                    if (data.status === 'sukses' && data.reply) {
                        var html = renderMarkdown(data.reply);
                        addMsg(html, 'bot', true);
                    } else {
                        var errMsg = data.message || 'Terjadi kesalahan. Silakan coba lagi.';
                        addMsg(errMsg, 'bot', false, true);
                    }
                })
                .catch(function (err) {
                    typing.remove();
                    console.error('Copilot error:', err);
                    addMsg('Gagal menghubungi server. Periksa koneksi Anda.', 'bot', false, true);
                })
                .finally(function () {
                    isSending = false;
                    sendBtn.disabled = false;
                    input.focus();
                });
        };

        // ── Keydown handler ──
        window.copilotKeyDown = function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                copilotSendMessage();
            }
        };

        // ── Add message to chat ──
        function addMsg(content, type, isHtml, isError) {
            var div = document.createElement('div');
            div.className = 'copilot-msg ' + type;
            if (isError) div.className += ' error';

            if (isHtml) {
                div.innerHTML = content;
            } else {
                div.textContent = content;
            }
            body.appendChild(div);
            scrollBottom();
        }

        function scrollBottom() {
            setTimeout(function () { body.scrollTop = body.scrollHeight; }, 50);
        }

        // ── Simple Markdown Renderer ──
        function renderMarkdown(text) {
            // Escape HTML first
            var html = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Code blocks (```)
            html = html.replace(/```[\s\S]*?```/g, function (match) {
                var code = match.replace(/```\w*\n?/g, '').replace(/```$/g, '');
                return '<code style="display:block;white-space:pre-wrap;padding:8px;background:#1a1a1a;border-radius:6px;margin:6px 0">' + code + '</code>';
            });

            // Inline code
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

            // Bold
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

            // Italic
            html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

            // Tables (simple markdown table parsing)
            html = html.replace(/((?:^\|.+\|$\n?)+)/gm, function (tableBlock) {
                var rows = tableBlock.trim().split('\n');
                if (rows.length < 2) return tableBlock;

                var tableHtml = '<table>';
                rows.forEach(function (row, idx) {
                    // Skip separator row (|---|---|)
                    if (/^\|[\s\-:|]+\|$/.test(row)) return;

                    var cells = row.split('|').filter(function (c, i, arr) {
                        return i > 0 && i < arr.length - 1;
                    });
                    var tag = (idx === 0) ? 'th' : 'td';
                    tableHtml += '<tr>';
                    cells.forEach(function (cell) {
                        tableHtml += '<' + tag + '>' + cell.trim() + '</' + tag + '>';
                    });
                    tableHtml += '</tr>';
                });
                tableHtml += '</table>';
                return tableHtml;
            });

            // Unordered lists
            html = html.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');
            html = html.replace(/((<li>.*<\/li>\n?)+)/g, function (m) {
                return '<ul>' + m + '</ul>';
            });

            // Ordered lists
            html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

            // Headers
            html = html.replace(/^### (.+)$/gm, '<strong style="font-size:14px;color:#a5b4fc">$1</strong>');
            html = html.replace(/^## (.+)$/gm, '<strong style="font-size:15px;color:#a5b4fc">$1</strong>');
            html = html.replace(/^# (.+)$/gm, '<strong style="font-size:16px;color:#a5b4fc">$1</strong>');

            // Line breaks (double newline = paragraph, single = br)
            html = html.replace(/\n\n/g, '</p><p>');
            html = html.replace(/\n/g, '<br>');

            // Wrap in paragraph
            html = '<p>' + html + '</p>';

            // Clean empty paragraphs
            html = html.replace(/<p>\s*<\/p>/g, '');

            return html;
        }
    })();
</script>