<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/** php artisan chatbot:test  -> shows why the chatbot is not using Gemini. */
class ChatbotTest extends Command
{
    protected $signature = 'chatbot:test';

    protected $description = 'Check the Gemini chatbot settings and make one test call';

    public function handle(): int
    {
        $provider = config('chatbot.provider');
        $key = config('chatbot.api_key');
        $model = config('chatbot.model') ?: 'gemini-3.5-flash';

        $this->line("Provider : {$provider}".($provider === 'gemini' ? '' : '   <-- should be "gemini" (check .env, then php artisan config:clear)'));
        $this->line('API key  : '.($key ? 'set ('.strlen($key).' characters, starts with '.substr($key, 0, 4).'...)' : 'EMPTY   <-- set CHATBOT_API_KEY in .env'));
        $this->line("Model    : {$model}");

        if (! $key) {
            return self::FAILURE;
        }

        try {
            $r = Http::withHeaders(['x-goog-api-key' => $key])->timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['role' => 'user', 'parts' => [['text' => 'Say hello in one short sentence.']]]],
                ]);
        } catch (\Throwable $e) {
            $this->error('Could not reach Gemini: '.$e->getMessage());
            $this->line('If this mentions "SSL certificate" (cURL error 60), set curl.cainfo in php.ini to a cacert.pem file and restart Apache.');

            return self::FAILURE;
        }

        if ($r->failed()) {
            $this->error('Gemini answered HTTP '.$r->status().': '.($r->json('error.message') ?? $r->body()));

            return self::FAILURE;
        }

        $this->info('OK. Gemini says: '.collect($r->json('candidates.0.content.parts', []))->pluck('text')->implode(''));

        return self::SUCCESS;
    }
}
