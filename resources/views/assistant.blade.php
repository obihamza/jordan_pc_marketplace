@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.seller')

@section('title', 'Assistant')

@section('content')
@include('partials.page-header', ['title' => 'Assistant'])

{{-- Same assistant as the chat button on the website, as a full page for the dashboards.
     It talks to chatbot.message -> ChatbotService (config/chatbot.php), where the AI model plugs in. --}}
<div class="card jo-assistant">
    <div class="card-header d-flex align-items-center gap-2">
        <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="" style="height: 28px;">
        <div class="lh-sm">
            <div class="fw-bold">JO Assistant</div>
            <div class="small text-muted fw-normal">Ask me anything</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="assistantClear"><i class="bi bi-arrow-counterclockwise"></i> New chat</button>
    </div>

    <div class="jo-assistant-body" id="assistantBody" aria-live="polite"></div>

    <form class="jo-assistant-form" id="assistantForm" autocomplete="off">
        <input type="text" id="assistantInput" class="form-control" placeholder="Type your message..." maxlength="1000" required aria-label="Your message">
        <button type="submit" class="btn btn-primary px-4">Send</button>
    </form>
    <div class="jo-chat-note bg-transparent">Basic answers for now. AI-powered replies are coming soon.</div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var endpoint = @json(route('chatbot.message'));
        var storageKey = 'jo_chat_history';           // same key as the website widget: one conversation
        var maxHistory = {{ (int) config('chatbot.max_history', 10) }};
        var greeting = 'Hi! I\'m the JO-Marketplace assistant. How can I help you today?';

        var body = document.getElementById('assistantBody');
        var form = document.getElementById('assistantForm');
        var input = document.getElementById('assistantInput');
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        var history = [];
        try { history = JSON.parse(sessionStorage.getItem(storageKey) || '[]'); } catch (e) { history = []; }
        function save() { try { sessionStorage.setItem(storageKey, JSON.stringify(history.slice(-30))); } catch (e) {} }

        function addMessage(role, text, extraClass) {
            var el = document.createElement('div');
            el.className = 'jo-chat-msg ' + (role === 'user' ? 'user' : 'bot') + (extraClass ? ' ' + extraClass : '');
            el.textContent = text;                    // textContent: never inject HTML from replies
            body.appendChild(el);
            body.scrollTop = body.scrollHeight;
            return el;
        }

        function render() {
            body.innerHTML = '';
            addMessage('assistant', greeting);
            history.forEach(function (m) { addMessage(m.role, m.content); });
        }

        document.getElementById('assistantClear').addEventListener('click', function () {
            history = [];
            save();
            render();
            input.focus();
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = input.value.trim();
            if (! text) { return; }

            input.value = '';
            addMessage('user', text);
            var context = history.slice(-maxHistory);
            history.push({ role: 'user', content: text });

            var typing = addMessage('assistant', 'Typing...', 'typing');

            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ message: text, history: context })
            })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (r) {
                typing.remove();
                var reply = r.ok && r.data.reply ? r.data.reply : 'Sorry, something went wrong. Please try again.';
                addMessage('assistant', reply);
                history.push({ role: 'assistant', content: reply });
                save();
            })
            .catch(function () {
                typing.remove();
                addMessage('assistant', 'Sorry, I could not reach the server. Please try again.');
                history.pop();
            });
        });

        render();
        input.focus();
    })();
</script>
@endpush
