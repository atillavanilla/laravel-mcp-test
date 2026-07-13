<?php

namespace App\Actions;

use App\Models\ProductSize;
use Illuminate\Support\Str;

class ResolveProductSize
{
    public const DEFAULT_SIZE = '-';

    public function execute(int $productId, ?string $size): ProductSize
    {
        $name = trim((string) $size);

        if ($name === '') {
            $name = self::DEFAULT_SIZE;
        }

        $code = $this->codeFromName($name);

        return ProductSize::query()->firstOrCreate(
            [
                'product_id' => $productId,
                'code' => $code,
            ],
            ['name' => $name]
        );
    }

    private function codeFromName(string $name): string
    {
        if ($name === self::DEFAULT_SIZE) {
            return self::DEFAULT_SIZE;
        }

        $code = Str::of($name)
            ->trim()
            ->upper()
            ->replaceMatches('/\s+/', '-')
            ->limit(50, '')
            ->toString();

        return $code === '' ? self::DEFAULT_SIZE : $code;
    }
}
