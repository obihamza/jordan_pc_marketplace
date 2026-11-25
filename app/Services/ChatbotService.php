<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The single place where the chatbot "thinks".
 *
 * The widget and ChatbotController never need to change when you add AI:
 * only askProvider() below does.
 */
class ChatbotService
{
    /**
     * @param  string  $message  What the visitor just typed.
     * @param  array<int, array{role: string, content: string}>  $history  Earlier messages (role is "user" or "assistant").
     * @param  User|null  $user  The logged-in user, or null for guests.
     */
    public function reply(string $message, array $history = [], ?User $user = null): string
    {
        $provider = config('chatbot.provider', 'placeholder');

        if ($provider !== 'placeholder') {
            try {
                return $this->askProvider($message, $history, $user);
            } catch (Throwable $e) {
                // Never show provider errors to visitors; fall back to the built-in answers.
                Log::warning('Chatbot provider failed: '.$e->getMessage());
            }
        }

        return $this->placeholderReply($message, $user);
    }

    /**
     * >>> AI INTEGRATION POINT <<<
     *
     * Call your AI model here and return its answer as plain text.
     * Available to you:
     *   - config('chatbot.provider'), config('chatbot.api_key'), config('chatbot.model')
     *   - $this->systemPrompt($user)  -> instructions + who is asking
     *   - $history + $message         -> the conversation so far
     *
     * Sketch (HTTP API; adjust URL/headers/body to your provider):
     *
     *   $response = Http::withToken(config('chatbot.api_key'))
     *       ->timeout(20)
     *       ->post('https://api.example.com/v1/chat', [
     *           'model'    => config('chatbot.model'),
     *           'messages' => [
     *               ['role' => 'system', 'content' => $this->systemPrompt($user)],
     *               ...$history,
     *               ['role' => 'user', 'content' => $message],
     *           ],
     *       ])->throw();
     *
     *   return $response->json('reply');   // path depends on the provider
     *
     * Throwing an exception is fine: reply() catches it and uses the placeholder answers.
     */
    protected function askProvider(string $message, array $history, ?User $user): string
    {
        throw new \RuntimeException('Chatbot provider "'.config('chatbot.provider').'" is not implemented yet.');
    }

    /** System prompt = the configured instructions + who is asking (no private data). */
    protected function systemPrompt(?User $user): string
    {
        $who = 'The visitor is not logged in.';

        if ($user) {
            $role = $user->isAdmin() ? 'an admin' : ($user->isSeller() ? 'a seller (who can also buy)' : 'a buyer');
            $who = 'The visitor is logged in as '.$role.'.';
        }

        return config('chatbot.system_prompt').' '.$who;
    }

    /** Simple built-in answers until a real AI provider is connected. */
    protected function placeholderReply(string $message, ?User $user): string
    {
        $text = mb_strtolower($message);

        // Whole-word match, so "they" doesn't count as "hey" and "code" isn't "cod".
        $has = fn (array $words) => (bool) preg_match(
            '/\b(?:'.implode('|', array_map('preg_quote', $words)).')\b/u',
            $text
        );

        if ($has(['hello', 'hi', 'hey', 'مرحبا', 'السلام'])) {
            $name = $user ? ', '.explode(' ', $user->name)[0] : '';

            return "Hello{$name}! I'm the JO-Marketplace assistant. Ask me about orders, payments, coupons or selling.";
        }

        if ($has(['coupon', 'coupons', 'discount', 'promo', 'code', 'خصم'])) {
            return 'Have a coupon? Enter its code on the checkout page before placing your order. The discount is applied if the coupon is valid and your order reaches its minimum amount.';
        }

        if ($has(['pay', 'paid', 'payment', 'payments', 'cash', 'دفع'])) {
            return "All orders are paid in cash on delivery, in Jordanian dinars (JOD). There's nothing to pay online.";
        }

        if ($has(['sell', 'selling', 'seller', 'sellers', 'list', 'بيع'])) {
            return "To sell, log in and choose \"Become a seller\" (you'll need your national ID and a shop name). Then add your products from the Selling menu.";
        }

        if ($has(['cancel', 'refund', 'return'])) {
            return 'You can cancel an order yourself while it is still pending: open it from "My orders" and press "Cancel order".';
        }

        if ($has(['order', 'orders', 'track', 'delivery', 'deliver', 'shipping', 'ship', 'طلب'])) {
            return 'You can follow your orders from "My orders" in the account menu. Each order shows its status: pending, confirmed, processing, shipped or delivered.';
        }

        if ($has(['account', 'login', 'register', 'signup', 'password'])) {
            return 'Use the Register button to create a free account, or Login if you already have one.';
        }

        return "I'm still learning! Smarter AI answers are coming soon. For now I can help with orders, payments, coupons and selling.";
    }
}
