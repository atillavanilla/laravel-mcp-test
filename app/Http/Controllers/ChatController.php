<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
// use Prism\Prism\Providers\Provider;
use App\Mcp\Tools\CreatePostsTool;
use App\Mcp\Tools\EditPost;
use App\Mcp\Tools\SearchPostsTool;
use Prism\Prism\Facades\Tool;

class ChatController extends Controller
{
    //

    public function __invoke(Request $request)
    {
        $userMessage = $request->input('message');

        // This is your MCP Client in action!
        $response = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.5-flash')
            ->withTools([
                Tool::make(CreatePostsTool::class),
                Tool::make(EditPost::class),
                Tool::make(SearchPostsTool::class),
                // new CreatePostsTool(),
                // new EditPost(),
                // new SearchPostsTool(),
            ])
            // ->withMaxSteps(5)
            ->withPrompt($userMessage)
            ->asText();

        return response()->json([
            'answer' => $response->text,
            'steps'  => $response->steps, // Optional: Shows which tools were called
        ]);
    }
}
