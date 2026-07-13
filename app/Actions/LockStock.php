<?php

namespace App\Actions;

use App\Models\ProductPrice;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LockStock
{
    public function execute(Stock $stock): bool
    {
        return DB::transaction(static function () use ($stock): bool {
            $stock = Stock::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($stock->id);

            if ($stock->status === 'locked') {
                throw new InvalidArgumentException('Stock is already locked.');
            }

            $now = now();

            $stock->status = 'locked';
            $stock->locked_at = $now;

            if (! $stock->save()) {
                return false;
            }

            $priceRows = [];

            foreach ($stock->items as $item) {
                $priceRows[$item->product_id.':'.$item->product_size_id] = [
                    'product_id' => $item->product_id,
                    'product_size_id' => $item->product_size_id,
                    'price' => $item->unit_price,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($priceRows !== []) {
                ProductPrice::query()->upsert(
                    array_values($priceRows),
                    ['product_id', 'product_size_id'],
                    ['price', 'updated_at']
                );
            }

            return true;
        });
    }
}
