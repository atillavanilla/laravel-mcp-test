<?php

namespace App\Mcp\Tools;

use App\Mcp\Resources\ApariUI;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;

#[RendersApp(resource: ApariUI::class)]
#[Description('This tool is used for showing the Apari manager UI.')]
class ShowApariManagerUI extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        //

        return Response::text('The Apari manager UI loaded.');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
