<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ApariManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;

class ChatController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['sometimes', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $user = auth()->user();
        // dd($user);

        $userMessage = $validated['message'];
        $history = collect($validated['history'] ?? [])
            ->take(-10)
            ->map(fn (array $message) => ucfirst($message['role']).': '.$message['content'])
            ->implode("\n");

        $prompt = trim($history."\nUser: ".$userMessage);

        try {
            $response = ApariManager::make(user: $user)->prompt($prompt, provider: [
                Lab::Gemini->value => 'gemini-2.5-flash',
                // Lab::OpenAI->value => 'gpt-4o-mini',
            ]);
        } catch (ProviderOverloadedException $e) {
            Log::warning('AI provider overloaded: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'The AI providers are overloaded right now. Please try again in a moment.',
            ], 503);
        } catch (RateLimitedException $e) {
            Log::warning('AI provider rate limited: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'The AI provider is rate limited. Please try again shortly.',
            ], 429);
        } catch (AiException $e) {
            Log::error('AI exception occurred: '.$e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Apari could not reply right now. Please try again.',
            ], 502);
        }

        return response()->json([
            'answer' => $response->text,
            'steps'  => $response->steps, // Optional: Shows which tools were called
        ]);
    }
}
