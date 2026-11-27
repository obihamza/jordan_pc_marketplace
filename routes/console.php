<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* php artisan chatbot:test  -> checks the connection to Gemini and every model the chatbot may use. */
Artisan::command('chatbot:test', function () {
    $key = config('chatbot.api_key');
    $this->line('Provider : '.config('chatbot.provider').(config('chatbot.provider') === 'gemini' ? '' : '   <-- should be "gemini" (check .env, then php artisan config:clear)'));
    $this->line('API key  : '.($key ? 'set ('.strlen($key).' characters)' : 'EMPTY   <-- set CHATBOT_API_KEY in .env'));

    if (! $key) {
        return 1;
    }

    $http = fn (int $seconds) => \Illuminate\Support\Facades\Http::withHeaders(['x-goog-api-key' => $key])->connectTimeout(8)->timeout($seconds);
    $base = 'https://generativelanguage.googleapis.com/v1beta/models';

    // 1) Plain connection + which models this key can use.
    $this->line('');
    $this->line('1) Connection to Google...');
    try {
        $t = microtime(true);
        $r = $http(15)->get($base, ['pageSize' => 200]);
        $this->line('   HTTP '.$r->status().' in '.round(microtime(true) - $t, 1).' s');

        if ($r->successful()) {
            $names = collect($r->json('models', []))->pluck('name')->map(fn ($n) => str_replace('models/', '', $n))
                ->filter(fn ($n) => str_contains($n, 'gemini') && str_contains($n, 'flash'))->values();
            $this->line('   Flash models your key can use: '.($names->implode(', ') ?: 'none found'));
        } else {
            $this->error('   '.($r->json('error.message') ?? $r->body()));
        }
    } catch (\Throwable $e) {
        $this->error('   Cannot reach Google: '.$e->getMessage());
        $this->line('   -> Your computer/network (firewall, antivirus, VPN or XAMPP SSL setup) is blocking or slowing the connection.');
        $this->line('   -> If it says "SSL certificate" (cURL error 60), set curl.cainfo in php.ini to a cacert.pem file and restart Apache.');

        return 1;
    }

    // 2) One tiny question to each model.
    $models = array_values(array_unique(array_filter([
        config('chatbot.model') ?: 'gemini-3.6-flash',
        ...array_map('trim', explode(',', (string) config('chatbot.fallback_models'))),
    ])));

    $this->line('');
    $this->line('2) Asking each model a tiny question (20 s limit each)...');
    foreach ($models as $model) {
        $t = microtime(true);
        try {
            $r = $http(20)->post("$base/{$model}:generateContent", [
                'contents' => [['role' => 'user', 'parts' => [['text' => 'Say hello in one short sentence.']]]],
            ]);
            $secs = round(microtime(true) - $t, 1);

            if ($r->successful()) {
                $this->info("   {$model}: OK in {$secs} s -> ".trim(collect($r->json('candidates.0.content.parts', []))->pluck('text')->implode('')));
            } else {
                $this->warn("   {$model}: HTTP {$r->status()} in {$secs} s - ".($r->json('error.message') ?? $r->body()));
            }
        } catch (\Throwable $e) {
            $this->warn("   {$model}: no answer in ".round(microtime(true) - $t, 1).' s ('.\Illuminate\Support\Str::limit($e->getMessage(), 80).')');
        }
    }

    return 0;
})->purpose('Check the connection to Gemini and every chatbot model');
