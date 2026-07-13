<?php

namespace App\Mcp\Tools;

use App\Actions\CreateProducts;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateProductsTool extends Tool
{
    protected string $description = 'Create products in bulk. The products are created with their associated sizes and categories. And the products has to be unique by name and size within the same category.';

    public function handle(Request $request, CreateProducts $action): Response
    {
        $data = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_category_id' => 'required|exists:product_categories,id',
            'products.*.name' => 'required|string|max:255',
            'products.*.size' => 'nullable|string|max:255',
            'products.*.sizes' => 'nullable|array',
            'products.*.sizes.*' => 'string|max:255',
            'products.*.description' => 'nullable|string',
            'products.*.is_active' => 'nullable|boolean',
        ]);

        $products = $action->execute($data['products']);

        return Response::json([
            'message' => 'Products created successfully.',
            'count' => $products->count(),
            'products' => $products->map(static fn ($product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'uucode' => $product->uucode,
                'size' => [
                    'id' => $product->resolved_product_size_id,
                    'name' => $product->resolved_product_size_name,
                    'code' => $product->resolved_product_size_code,
                ],
                'sizes' => $product->resolved_product_sizes->map(static fn ($size): array => [
                    'id' => $size->id,
                    'name' => $size->name,
                    'code' => $size->code,
                ])->all(),
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'products' => $schema->array()
                ->items($schema->object([
                    'product_category_id' => $schema->integer()->required()->description('The category id for the product.'),
                    'name' => $schema->string()->required()->description('The product name.'),
                    'size' => $schema->string()->nullable()->description('Optional single size name/code. Missing or blank values resolve to "-".'),
                    'sizes' => $schema->array()
                        ->items($schema->string())
                        ->description('Optional list of size names/codes for this product. If provided, each size is created for the product.'),
                    'description' => $schema->string()->nullable()->description('Optional product description.'),
                    'is_active' => $schema->boolean()->nullable()->description('Whether the product is active.'),
                ])->withoutAdditionalProperties())
                ->min(1)
                ->description('A list of product payloads to create.'),
        ];
    }
}
