<button type="button" class="jo-chat-fab" id="joChatToggle" aria-label="Open chat assistant" aria-expanded="false" aria-controls="joChatPanel">
    <svg id="joChatIconOpen" xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
        <path d="M2 1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h9.586a2 2 0 0 1 1.414.586l2 2V2a1 1 0 0 0-1-1zm12-1a2 2 0 0 1 2 2v12.793a.5.5 0 0 1-.854.353l-2.853-2.853a1 1 0 0 0-.707-.293H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/>
        <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
    </svg>
    <svg id="joChatIconClose" xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" style="display:none">
        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
    </svg>
</button>

<section class="jo-chat-panel" id="joChatPanel" role="dialog" aria-label="JO-Marketplace assistant">
    <div class="jo-chat-head">
        <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="">
        <div class="lh-sm">
            <div class="fw-bold">JO Assistant</div>
            <div class="small">Ask me anything</div>
        </div>
        <button type="button" class="btn-close" id="joChatClose" aria-label="Close chat"></button>
    </div>

    <div class="jo-chat-body" id="joChatBody" aria-live="polite"></div>

    <div class="jo-chat-chips" id="joChatChips"></div>

    <form class="jo-chat-form" id="joChatForm" autocomplete="off">
        <input type="text" id="joChatInput" class="form-control" placeholder="Type your message..." maxlength="1000" required aria-label="Your message">
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
    <div class="jo-chat-note">Powered by AI. Answers may contain mistakes.</div>
</section>

<script src="{{ asset('js/jo-markdown.js') }}"></script>
<script src="{{ asset('js/jo-chat.js') }}"></script>
<script>
    (function () {
        var toggle = document.getElementById('joChatToggle');
        var panel = document.getElementById('joChatPanel');
        var body = document.getElementById('joChatBody');
        var input = document.getElementById('joChatInput');
        var iconOpen = document.getElementById('joChatIconOpen');
        var iconClose = document.getElementById('joChatIconClose');

        joChat({
            endpoint: @json(route('chatbot.message')),
            storageKey: 'jo_chat_history',
            maxHistory: {{ (int) config('chatbot.max_history', 10) }},
            greeting: 'Hi! I\'m the JO-Marketplace assistant. How can I help you today?',
            suggestions: @json(\App\Services\ChatbotInsights::suggestions(auth()->user())),
            body: body,
            form: document.getElementById('joChatForm'),
            input: input,
            chips: document.getElementById('joChatChips')
        });

        function setOpen(open) {
            panel.classList.toggle('open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close chat assistant' : 'Open chat assistant');
            iconOpen.style.display = open ? 'none' : '';
            iconClose.style.display = open ? '' : 'none';
            if (open) { body.scrollTop = body.scrollHeight; input.focus(); }
        }

        toggle.addEventListener('click', function () { setOpen(! panel.classList.contains('open')); });
        document.getElementById('joChatClose').addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setOpen(false); } });
    })();
</script>
