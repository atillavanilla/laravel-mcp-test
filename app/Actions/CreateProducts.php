<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProducts
{
    public const INSERT_CHUNK_SIZE = 100;

    public function __construct(private readonly ResolveProductSize $resolveProductSize)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return Collection<int, Product>
     */
    public function execute(array $items): Collection
    {
        if ($items === []) {
            return collect();
        }

        return DB::transaction(function () use ($items): Collection {
            $now = now();
            $sizesByUucode = [];
            $resolvedSizesByUucode = [];
            $usedUucodes = [];

            $rows = array_map(static function (array $item) use ($now, &$sizesByUucode, &$usedUucodes): array {
                $uucode = self::generateUniqueUucode($usedUucodes);
                $usedUucodes[] = $uucode;
                $sizesByUucode[$uucode] = self::sizesFromItem($item);

                unset($item['reference']);
                unset($item['sku']);
                unset($item['uucode']);
                unset($item['size']);
                unset($item['sizes']);

                return array_merge($item, [
                    'uucode' => $uucode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }, $items);

            foreach (array_chunk($rows, self::INSERT_CHUNK_SIZE) as $chunk) {
                Product::query()->insert($chunk);
            }

            $uucodes = array_column($rows, 'uucode');
            $products = Product::query()
                ->whereIn('uucode', $uucodes)
                ->get()
                ->keyBy('uucode');

            foreach ($uucodes as $uucode) {
                /** @var Product $product */
                $product = $products->get($uucode);
                $resolvedSizesByUucode[$uucode] = collect($sizesByUucode[$uucode])
                    ->map(fn (?string $size) => $this->resolveProductSize->execute($product->id, $size))
                    ->values();
            }

            return collect($uucodes)
                ->map(static function (string $uucode) use ($products, $resolvedSizesByUucode): Product {
                    /** @var Product $product */
                    $product = $products->get($uucode);
                    /** @var Collection<int, \App\Models\ProductSize> $sizes */
                    $sizes = $resolvedSizesByUucode[$uucode];
                    $firstSize = $sizes->first();

                    $product->setAttribute('resolved_product_sizes', $sizes);
                    $product->setAttribute('resolved_product_size_id', $firstSize->id);
                    $product->setAttribute('resolved_product_size_name', $firstSize->name);
                    $product->setAttribute('resolved_product_size_code', $firstSize->code);

                    return $product;
                })
                ->values();
        });
    }

    /**
     * @return array<int, string|null>
     */
    private static function sizesFromItem(array $item): array
    {
        if (isset($item['sizes']) && is_array($item['sizes']) && $item['sizes'] !== []) {
            return array_values(array_unique($item['sizes']));
        }

        return [$item['size'] ?? null];
    }

    /**
     * @param array<int, string> $usedUucodes
     */
    private static function generateUniqueUucode(array $usedUucodes): string
    {
        do {
            $uucode = 'UUC-'.Str::upper(Str::random(10));
        } while (in_array($uucode, $usedUucodes, true) || Product::query()->where('uucode', $uucode)->exists());

        return $uucode;
    }
}
