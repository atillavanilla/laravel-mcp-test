<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'status', 'notes', 'locked_at'];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (Stock $stock): void {
            if (blank($stock->reference)) {
                $stock->reference = self::generateUniqueReference();
            }
        });
    }

    /**
     * @param array<int, string> $reservedReferences
     */
    public static function generateUniqueReference(array $reservedReferences = []): string
    {
        do {
            $reference = 'STK-'.Str::upper(Str::random(10));
        } while (in_array($reference, $reservedReferences, true) || self::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
