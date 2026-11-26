<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* php artisan chatbot:test  -> shows why the chatbot is not using Gemini. */
Artisan::command('chatbot:test', function () {
    $provider = config('chatbot.provider');
    $key = config('chatbot.api_key');
    $model = config('chatbot.model') ?: 'gemini-3.6-flash';

    $this->line("Provider : {$provider}".($provider === 'gemini' ? '' : '   <-- should be "gemini" (check .env, then php artisan config:clear)'));
    $this->line('API key  : '.($key ? 'set ('.strlen($key).' characters, starts with '.substr($key, 0, 4).'...)' : 'EMPTY   <-- set CHATBOT_API_KEY in .env'));
    $this->line("Model    : {$model}");

    if (! $key) {
        return 1;
    }

    try {
        $r = \Illuminate\Support\Facades\Http::withHeaders(['x-goog-api-key' => $key])->timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [['role' => 'user', 'parts' => [['text' => 'Say hello in one short sentence.']]]],
            ]);
    } catch (\Throwable $e) {
        $this->error('Could not reach Gemini: '.$e->getMessage());
        $this->line('If this mentions "SSL certificate" (cURL error 60), set curl.cainfo in php.ini to a cacert.pem file and restart Apache.');

        return 1;
    }

    if ($r->failed()) {
        $this->error('Gemini answered HTTP '.$r->status().': '.($r->json('error.message') ?? $r->body()));

        return 1;
    }

    $this->info('OK. Gemini says: '.collect($r->json('candidates.0.content.parts', []))->pluck('text')->implode(''));

    return 0;
})->purpose('Check the Gemini chatbot settings and make one test call');
