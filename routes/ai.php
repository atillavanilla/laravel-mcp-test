<?php

use Laravel\Mcp\Facades\Mcp;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
Mcp::web('posts', \App\Mcp\Servers\PostServer::class);

