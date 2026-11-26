<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint used by the chat widget (open to guests and logged-in users).
 * All the "thinking" lives in App\Services\ChatbotService.
 */
class ChatbotController extends Controller
{
    /** Full-page assistant for the admin and seller dashboards (they have no floating chat button). */
    public function page()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isSeller(), 403);

        return view('assistant');
    }

    public function message(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:'.(int) config('chatbot.max_history', 10),
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:2000',
        ]);

        $reply = $chatbot->reply(
            $validated['message'],
            $validated['history'] ?? [],
            $request->user()
        );

        return response()->json(['reply' => $reply]);
    }
}
