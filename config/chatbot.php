<?php

/*
|--------------------------------------------------------------------------
| JO-Marketplace chatbot
|--------------------------------------------------------------------------
|
| The chat widget (bottom-right on every page) talks to
| App\Http\Controllers\ChatbotController -> App\Services\ChatbotService.
|
| Provider "placeholder": a few built-in keyword answers (no key needed).
| Provider "gemini": Google Gemini. Put this in your .env, then run
| `php artisan config:clear`:
|
|   CHATBOT_PROVIDER=gemini
|   CHATBOT_API_KEY=your-key-from-aistudio.google.com/apikey
|   CHATBOT_MODEL=gemini-3.5-flash
|
| If Gemini is unreachable or errors, the placeholder answers are used and
| the reason is written to storage/logs/laravel.log.
|
*/

return [

    // 'placeholder' (built-in answers) or the name of an AI provider you implement.
    'provider' => env('CHATBOT_PROVIDER', 'placeholder'),

    'api_key' => env('CHATBOT_API_KEY'),

    'model' => env('CHATBOT_MODEL', 'gemini-3.5-flash'),

    // Seconds to wait for the AI before falling back to the built-in answers.
    'timeout' => (int) env('CHATBOT_TIMEOUT', 25),

    // How many previous messages the widget sends along, for conversation context.
    'max_history' => 10,

    // Instructions given to the AI model (ignored by the placeholder provider).
    'system_prompt' => env(
        'CHATBOT_SYSTEM_PROMPT',
        'You are the friendly shopping assistant of JO-Marketplace, an online marketplace in Jordan '.
        'where people buy and sell new and used items. Payment is cash on delivery (JOD). '.
        'Help with finding products, orders, selling on the site and account questions, and, for sellers and admins, '.
        'give business insight and analysis from the live data you are given. '.
        'Reply in the language the user writes in (Arabic or English). Keep answers short and practical; '.
        'you may use short lists and **bold**, but no tables or HTML. If you do not know something, say so.'
    ),
];
