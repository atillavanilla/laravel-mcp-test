<?php

namespace App\Mcp\Tools;

use App\Actions\CreateProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateProductCategoryTool extends Tool
{
    protected string $description = 'Create a product category for the inventory system.';

    public function handle(Request $request, CreateProductCategory $action): Response
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_categories,slug',
            'description' => 'nullable|string',
        ]);

        $action->execute($data);

        return Response::text('Product category created successfully.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('The category name.'),
            'slug' => $schema->string()->description('The category slug.'),
            'description' => $schema->string()->description('Optional category description.'),
        ];
    }
}
