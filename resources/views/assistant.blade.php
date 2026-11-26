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

    <div class="jo-chat-chips" id="assistantChips"></div>

    <form class="jo-assistant-form" id="assistantForm" autocomplete="off">
        <input type="text" id="assistantInput" class="form-control" placeholder="Type your message..." maxlength="1000" required aria-label="Your message">
        <button type="submit" class="btn btn-primary px-4">Send</button>
    </form>
    <div class="jo-chat-note bg-transparent">Powered by AI, with live insight about your own data. Answers may contain mistakes.</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/jo-markdown.js') }}"></script>
<script src="{{ asset('js/jo-chat.js') }}"></script>
<script>
    (function () {
        var input = document.getElementById('assistantInput');

        var chat = joChat({
            endpoint: @json(route('chatbot.message')),
            storageKey: 'jo_chat_history',              // same key as the website widget: one conversation
            maxHistory: {{ (int) config('chatbot.max_history', 10) }},
            greeting: 'Hi! I\'m the JO-Marketplace assistant. Ask me how the dashboard works, or about your sales and revenue.',
            suggestions: @json(\App\Services\ChatbotInsights::suggestions(auth()->user())),
            body: document.getElementById('assistantBody'),
            form: document.getElementById('assistantForm'),
            input: input,
            chips: document.getElementById('assistantChips')
        });

        document.getElementById('assistantClear').addEventListener('click', function () { chat.clear(); input.focus(); });
        input.focus();
    })();
</script>
@endpush
