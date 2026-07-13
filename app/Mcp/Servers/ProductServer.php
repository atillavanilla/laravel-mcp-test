<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateProductsTool;
use App\Mcp\Tools\CreateProductCategoriesTool;
use App\Mcp\Tools\CreateProductCategoryTool;
use App\Mcp\Tools\CreateProductSizeTool;
use App\Mcp\Tools\CreateStockTool;
use App\Mcp\Tools\ListStocksTool;
use App\Mcp\Tools\ListProductsTool;
use App\Mcp\Tools\LockStockTool;
use App\Mcp\Tools\ShowCategoriesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Product Server')]
#[Version('0.0.1')]
#[Instructions('Use this server to manage product categories, products, sizes, stocks, stock items, and lock stocks to sync prices. For large imports, list/search categories first, create missing categories in batches with the bulk category tool, then create products in repeated batches of up to 100 products per call. Do not refuse large imports solely because multiple tool calls are required.')]
class ProductServer extends Server
{
    protected array $tools = [
        CreateProductsTool::class,
        // CreateProductTool::class,
        CreateProductCategoriesTool::class,
        CreateProductCategoryTool::class,
        ShowCategoriesTool::class,
        CreateProductSizeTool::class,
        ListProductsTool::class,
        CreateStockTool::class,
        ListStocksTool::class,
        LockStockTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
