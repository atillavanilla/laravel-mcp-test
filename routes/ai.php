<?php

use Laravel\Mcp\Facades\Mcp;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
Mcp::local('posts', \App\Mcp\Servers\PostServer::class);


