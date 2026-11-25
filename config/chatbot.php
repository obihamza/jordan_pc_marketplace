<?php

/*
|--------------------------------------------------------------------------
| JO-Marketplace chatbot
|--------------------------------------------------------------------------
|
| The chat widget (bottom-right on every page) talks to
| App\Http\Controllers\ChatbotController -> App\Services\ChatbotService.
|
| Right now the provider is "placeholder": a few built-in keyword answers.
| To plug in a real AI model later:
|   1. set CHATBOT_PROVIDER / CHATBOT_API_KEY / CHATBOT_MODEL in your .env
|   2. implement ChatbotService::askProvider() (the only place that needs to change)
|
*/

return [

    // 'placeholder' (built-in answers) or the name of an AI provider you implement.
    'provider' => env('CHATBOT_PROVIDER', 'placeholder'),

    'api_key' => env('CHATBOT_API_KEY'),

    'model' => env('CHATBOT_MODEL'),

    // How many previous messages the widget sends along, for conversation context.
    'max_history' => 10,

    // Instructions given to the AI model (ignored by the placeholder provider).
    'system_prompt' => env(
        'CHATBOT_SYSTEM_PROMPT',
        'You are the friendly shopping assistant of JO-Marketplace, an online marketplace in Jordan '.
        'where people buy and sell new and used items. Payment is cash on delivery (JOD). '.
        'Help with finding products, orders, selling on the site and account questions. '.
        'Keep answers short. If you do not know something, say so.'
    ),
];
