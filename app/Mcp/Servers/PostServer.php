<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\ApariUI;
use App\Mcp\Tools\CreatePostsTool;
use App\Mcp\Tools\EditPost;
use App\Mcp\Tools\ListOrSearchPostsTool;
use App\Mcp\Tools\ShowApariManagerUI;
use Laravel\Mcp\Server;

class PostServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Post Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        This server allows you to create posts using title and content fields.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CreatePostsTool::class,
        EditPost::class,
        ListOrSearchPostsTool::class,
        ShowApariManagerUI::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        ApariUI::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
