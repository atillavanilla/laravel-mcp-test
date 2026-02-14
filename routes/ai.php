<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('posts', \App\Mcp\Servers\PostServer::class)
    ->middleware('auth:api');

