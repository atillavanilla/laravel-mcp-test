<?php

namespace App\Mcp\Tools;

use App\Actions\CreateStock;
use App\Models\ProductSize;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateStockTool extends Tool
{
    protected string $description = 'Create a stock and optionally receive stock items into it in one call. Stock references are generated internally and are always unique. Pass stock_id only when adding items to an existing open stock. Do not lock (status) the item unless it is explicitly stated in the user prompt.';

    public function handle(Request $request, CreateStock $action): Response
    {
        $data = $request->validate([
            'stock_id' => 'nullable|integer|exists:stocks,id',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.product_size_id' => 'required_with:items|exists:product_sizes,id',
            'items.*.quantity' => 'required_with:items|integer|min:0',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        foreach ($data['items'] ?? [] as $index => $item) {
            $sizeBelongsToProduct = ProductSize::query()
                ->whereKey($item['product_size_id'])
                ->where('product_id', $item['product_id'])
                ->exists();

            if (! $sizeBelongsToProduct) {
                throw ValidationException::withMessages([
                    "items.$index.product_size_id" => 'The selected size does not belong to the selected product.',
                ]);
            }
        }

        try {
            $stock = $action->execute($data);
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::json([
            'message' => 'Stock processed successfully.',
            'stock' => [
                'id' => $stock->id,
                'reference' => $stock->reference,
                'status' => $stock->status,
                'notes' => $stock->notes,
                'item_count' => $stock->items->count(),
            ],
            'items' => $stock->items->map(static fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_size_id' => $item->product_size_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'stock_id' => $schema->integer()->nullable()->description('Optional existing open stock id to receive items into. Locked stocks are rejected.'),
            'status' => $schema->string()->nullable()->description('The stock status for a new stock, or optional status update for an existing open stock. Defaults to draft.'),
            'notes' => $schema->string()->nullable()->description('Optional notes for a new stock, or optional notes update for an existing open stock.'),
            'items' => $schema->array()
                ->items($schema->object([
                    'product_id' => $schema->integer()->required()->description('The product id to stock.'),
                    'product_size_id' => $schema->integer()->required()->description('The size id.'),
                    'quantity' => $schema->integer()->required()->description('The quantity being stocked.'),
                    'unit_price' => $schema->number()->required()->description('The unit price for the stock entry.'),
                ])->withoutAdditionalProperties())
                ->description('Optional list of stock items to receive. Records are inserted internally in chunks of 100.'),
        ];
    }
}
