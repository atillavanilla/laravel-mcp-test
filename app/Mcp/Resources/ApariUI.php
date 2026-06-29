<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('An interactive UI for managing Apari resources.')]
#[AppMeta]
class ApariUI extends AppResource
{
    /**
     * The app resource's title.
     */
    protected string $title = 'Apari Manager';

    /**
     * Handle the app resource request.
     */
    public function handle(Request $request): Response
    {
        return Response::view('mcp.apari-u-i', [
            'title' => $this->title(),
        ]);
    }
}
