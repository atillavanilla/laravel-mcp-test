<?php

namespace App\Mcp\Tools;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchPostsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Search for posts by title or content to find their IDs.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        //

        $validated = $request->validate([
            'query' => 'required|string',
        ]);


        if (empty($validated['query']) || $validated['query'] === '*' || strtolower($validated['query']) === 'all') {
            $postQuery = Post::all();
        }else {
            $postQuery = Post::where('title', 'like', '%' . $validated['query'] . '%')
                ->orWhere('content', 'like', '%' . $validated['query'] . '%')
                ->get();
        }
        if ($postQuery->isEmpty()) {
            return Response::text('No posts found matching the query.');
        }

        return Response::text($postQuery->toJson(JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
            'query' => $schema->string()->description('The search query for posts.')->required(),
        ];
    }
}
