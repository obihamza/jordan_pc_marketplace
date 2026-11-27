<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function page()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isSeller(), 403);

        return view('assistant');
    }

    private function validateMessage(Request $request): array
    {
        return $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:'.(int) config('chatbot.max_history', 10),
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:2000',
        ]);
    }

    public function message(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $this->validateMessage($request);

        $reply = $chatbot->reply(
            $validated['message'],
            $validated['history'] ?? [],
            $request->user()
        );

        return response()->json(['reply' => $reply]);
    }
}
