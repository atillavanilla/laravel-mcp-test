<?php

use App\Mcp\Servers\PostServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('posts', PostServer::class)
    ->middleware('auth:api');

