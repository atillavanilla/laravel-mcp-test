<?php

use App\Mcp\Servers\PostServer;
use App\Mcp\Servers\ProductServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('posts', PostServer::class)
    ->middleware('auth:api');

Mcp::web('products', ProductServer::class)
    ->middleware('auth:api');