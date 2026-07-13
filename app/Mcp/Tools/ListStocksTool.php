<?php

namespace App\Mcp\Tools;

use App\Models\Stock;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListStocksTool extends Tool
{
    protected string $description = "List stocks by open, locked, or all status. Can include item counts, total stocked quantity, and item details.";

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'query' => 'nullable|string|max:255',
            'status_filter' => 'nullable|string|in:open,locked,all',
            'stock_ids' => 'nullable|array',
            'stock_ids.*' => 'integer|exists:stocks,id',
            'include_items' => 'nullable|boolean',
        ]);

        $search = trim((string) ($data['query'] ?? ''));
        $statusFilter = $data['status_filter'] ?? 'open';
        $includeItems = (bool) ($data['include_items'] ?? false);

        $query = Stock::query()
            ->withCount('items')
            ->withSum('items as total_quantity', 'quantity')
            ->latest('id');

        if ($includeItems) {
            $query->with([
                'items' => static function ($query): void {
                    $query
                        ->with(['product.category', 'size'])
                        ->latest('id');
                },
            ]);
        }

        if ($statusFilter === 'open') {
            $query->whereNull('locked_at')
                ->where('status', '!=', 'locked');
        }

        if ($statusFilter === 'locked') {
            $query->where(static function ($query): void {
                $query
                    ->whereNotNull('locked_at')
                    ->orWhere('status', 'locked');
            });
        }

        if ($search !== '' && $search !== '*' && strtolower($search) !== 'all') {
            $query->where(static function ($query) use ($search): void {
                $query
                    ->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhere('notes', 'like', '%'.$search.'%');
            });
        }

        if (! empty($data['stock_ids'])) {
            $query->whereIn('id', $data['stock_ids']);
        }

        $stocks = $query->get();

        return Response::json([
            'count' => $stocks->count(),
            'stocks' => $stocks->map(static function (Stock $stock) use ($includeItems): array {
                $payload = [
                    'id' => $stock->id,
                    'reference' => $stock->reference,
                    'status' => $stock->status,
                    'is_locked' => $stock->locked_at !== null || $stock->status === 'locked',
                    'locked_at' => $stock->locked_at,
                    'notes' => $stock->notes,
                    'item_count' => $stock->items_count,
                    'total_quantity' => (int) ($stock->total_quantity ?? 0),
                ];

                if ($includeItems) {
                    $payload['items'] = $stock->items->map(static fn ($item): array => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product' => $item->product?->name,
                        'category' => $item->product?->category?->name,
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
            'query' => $schema->string()->nullable()->description("Optional search text for reference, status, or notes. Pass '*', blank, or 'all' with status_filter='all' to list every stock."),
            'status_filter' => $schema->string()->nullable()->description("Which stocks to return: 'open', 'locked', or 'all'. Defaults to open."),
            'stock_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Optional stock ids to return.'),
            'include_items' => $schema->boolean()->nullable()->description('Whether to include stocked item details for each stock.'),
        ];
    }
}
