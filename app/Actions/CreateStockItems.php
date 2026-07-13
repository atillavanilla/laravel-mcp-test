<?php

namespace App\Actions;

use App\Models\ProductSize;
use App\Models\Stock;
use App\Models\StockItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateStockItems
{
    public const INSERT_CHUNK_SIZE = 100;

    public function execute(array $data): Stock
    {
        return DB::transaction(function () use ($data): Stock {
            $now = now();
            $stock = $this->resolveStock($data, $now);

            $items = $data['items'] ?? [];

            $this->validateProductSizes($items);

            foreach (array_chunk($items, self::INSERT_CHUNK_SIZE) as $chunk) {
                StockItem::query()->insert(array_map(static fn (array $item): array => [
                    'stock_id' => $stock->id,
                    'product_id' => $item['product_id'],
                    'product_size_id' => $item['product_size_id'],
                    'quantity' => $item['quantity'] ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));
            }

            return Stock::query()->with('items')->findOrFail($stock->id);
        });
    }

    private function resolveStock(array $data, CarbonInterface $now): Stock
    {
        if (! empty($data['stock_id'])) {
            $stock = Stock::query()
                ->lockForUpdate()
                ->findOrFail($data['stock_id']);
        } else {
            $stock = Stock::query()->create([
                'status' => $data['status'] ?? 'draft',
                'notes' => $data['notes'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($stock->status === 'locked' || $stock->locked_at !== null) {
            throw new InvalidArgumentException('Cannot add items to a locked stock.');
        }

        if ($stock->exists && (! empty($data['status']) || array_key_exists('notes', $data))) {
            $stock->fill([
                'status' => $data['status'] ?? $stock->status,
                'notes' => $data['notes'] ?? $stock->notes,
            ])->save();
        }

        return $stock;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function validateProductSizes(array $items): void
    {
        foreach ($items as $item) {
            $sizeBelongsToProduct = ProductSize::query()
                ->whereKey($item['product_size_id'])
                ->where('product_id', $item['product_id'])
                ->exists();

            if (! $sizeBelongsToProduct) {
                throw new InvalidArgumentException('The selected size does not belong to the selected product.');
            }
        }
    }
}
