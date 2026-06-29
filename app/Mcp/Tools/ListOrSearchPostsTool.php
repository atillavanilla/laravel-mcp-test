<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListOrSearchPostsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Search for posts by title or content to find their IDs...you can pass * or . as a wildcard to search for all posts... all can be used as keyword to search for all posts';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        //

        $validated = $request->validate([
            'query' => 'required|string',
        ]);


        $user = auth('api')->user();

        $query = $user->posts()->select('id', 'title', 'content', 'created_at');

        if (!empty($validated['query']) && $validated['query'] !== '*' && strtolower($validated['query']) !== 'all') {
            $query->where(function ($q) use ($validated) {
                $q->where('title', 'like', '%' . $validated['query'] . '%')
                  ->orWhere('content', 'like', '%' . $validated['query'] . '%');
            });
        }

        $postQuery = $query->get();
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
