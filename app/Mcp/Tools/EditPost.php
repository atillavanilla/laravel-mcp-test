<?php

namespace App\Mcp\Tools;

use App\Actions\UpdatePost;
use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class EditPost extends Tool
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
    public function handle(Request $request, UpdatePost $action): Response
    {
        //

        $post = $request->validate([
            'id' => 'required|integer',
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
        ]);

        $editPost = Post::findOrFail($post['id']);

        if (!$editPost) {
            return Response::error('Post not found.');
        }

        $updateData = collect($post)->except('id')->toArray();

        $action->execute($editPost, $updateData);

        return Response::text("Post {$editPost->id} updated successfully.");
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
            'id' => $schema->integer()->description('The ID of the post to edit.')->required(),
            'title' => $schema->string()->description('The new title of the post.'),
            'content' => $schema->string()->description('The new content of the post.'),
        ];
    }
}
