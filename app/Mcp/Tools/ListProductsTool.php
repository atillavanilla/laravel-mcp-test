<?php

namespace App\Mcp\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListProductsTool extends Tool
{
    protected string $description = "List products, optionally searching by name or uucode and including created stock items. Pass '*', blank, or 'all' as a wildcard to list all products.";

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'query' => 'nullable|string|max:255',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'uucodes' => 'nullable|array',
            'uucodes.*' => 'string|max:255|exists:products,uucode',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.product_size_id' => 'nullable|integer|exists:product_sizes,id',
            'include_items' => 'nullable|boolean',
            'stock_id' => 'nullable|integer|exists:stocks,id',
        ]);

        $itemProductIds = collect($data['items'] ?? [])
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $itemSizeIds = collect($data['items'] ?? [])
            ->pluck('product_size_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $includeItems = (bool) ($data['include_items'] ?? false) || $itemProductIds !== [];
        $search = trim((string) ($data['query'] ?? ''));

        $query = Product::query()
            ->with(['category', 'sizes'])
            ->orderBy('name');

        if ($includeItems || isset($data['stock_id'])) {
            $query->with([
                'stockItems' => static function ($query) use ($data, $itemSizeIds) {
                    $query
                        ->when(isset($data['stock_id']), static fn ($query) => $query->where('stock_id', $data['stock_id']))
                        ->when($itemSizeIds !== [], static fn ($query) => $query->whereIn('product_size_id', $itemSizeIds))
                        ->with('size')
                        ->latest('id');
                },
            ]);
        }

        if ($search !== '' && $search !== '*' && strtolower($search) !== 'all') {
            $query->where(static function ($query) use ($search): void {
                $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('uucode', 'like', '%'.$search.'%');
            });
        }

        if (! empty($data['product_ids'])) {
            $query->whereIn('id', $data['product_ids']);
        }

        if ($itemProductIds !== []) {
            $query->whereIn('id', $itemProductIds);
        }

        if (! empty($data['uucodes'])) {
            $query->whereIn('uucode', $data['uucodes']);
        }

        $products = $query->get();

        return Response::json([
            'count' => $products->count(),
            'products' => $products->map(static function (Product $product) use ($includeItems, $data): array {
                $payload = [
                    'id' => $product->id,
                    'product_category_id' => $product->product_category_id,
                    'category' => $product->category?->name,
                    'name' => $product->name,
                    'uucode' => $product->uucode,
                    'description' => $product->description,
                    'is_active' => $product->is_active,
                    'sizes' => $product->sizes->map(static fn ($size): array => [
                        'id' => $size->id,
                        'name' => $size->name,
                        'code' => $size->code,
                        'sort_order' => $size->sort_order,
                    ])->all(),
                ];

                if ($includeItems || isset($data['stock_id'])) {
                    $payload['items'] = $product->stockItems->map(static fn ($item): array => [
                        'id' => $item->id,
                        'stock_id' => $item->stock_id,
                        'product_size_id' => $item->product_size_id,
                        'size' => $item->size?->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ])->all();
                }

                return $payload;
            })->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->nullable()->description("Optional search text for product name or uucode. Pass '*', blank, or 'all' to list all products."),
            'product_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Optional product ids to return.'),
            'uucodes' => $schema->array()
                ->items($schema->string())
                ->description('Optional product uucodes to return.'),
            'items' => $schema->array()
                ->items($schema->object([
                    'product_id' => $schema->integer()->required()->description('The product id from a stock item payload.'),
                    'product_size_id' => $schema->integer()->nullable()->description('Optional size id from a stock item payload.'),
                ])->withoutAdditionalProperties())
                ->description('Optional stock item payloads. When provided, only matching products are returned and their created items are included.'),
            'include_items' => $schema->boolean()->nullable()->description('Whether to include created stock items for each product.'),
            'stock_id' => $schema->integer()->nullable()->description('Optional stock id. When provided, only items from that stock are included.'),
        ];
    }
}
