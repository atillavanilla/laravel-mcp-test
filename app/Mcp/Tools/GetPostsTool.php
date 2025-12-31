<?php

namespace App\Mcp\Tools;

use App\Actions\CreatePost;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetPostsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        A description of what this tool does.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, CreatePost $createPost): Response
    {
        //
        // $posts = $request->only(['title', 'content']);
        $posts = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $createPost->execute($posts);

        return Response::text(<<<'MARKDOWN'
            Post created successfully.
        MARKDOWN);
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
            'title' => $schema->string()->description('The title of the post.'),
            'content' => $schema->string()->description('The content of the post.'),
        ];
    }
}
