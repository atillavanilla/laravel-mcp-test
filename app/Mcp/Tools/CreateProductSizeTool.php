<?php

namespace App\Mcp\Tools;

use App\Actions\CreateProductSize;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateProductSizeTool extends Tool
{
    protected string $description = 'Create a size option such as S, M, L.';

    public function handle(Request $request, CreateProductSize $action): Response
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_sizes', 'code')
                    ->where('product_id', $request->input('product_id')),
            ],
            'sort_order' => 'nullable|integer',
        ]);

        $action->execute($data);

        return Response::text('Product size created successfully.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->integer()->required()->description('The product id this size belongs to.'),
            'name' => $schema->string()->description('The display name of the size.'),
            'code' => $schema->string()->description('The short code of the size.'),
            'sort_order' => $schema->integer()->description('Optional size ordering.'),
        ];
    }
}
