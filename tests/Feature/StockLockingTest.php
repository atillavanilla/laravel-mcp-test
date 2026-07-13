<?php

use App\Actions\CreateProducts;
use App\Actions\CreateStockItems;
use App\Actions\LockStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductSize;
use App\Models\Stock;
use App\Models\StockItem;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('locking a stock creates product prices for each stocked item', function () {
    $category = ProductCategory::create([
        'name' => 'Apparel',
        'slug' => 'apparel',
    ]);

    $product = Product::create([
        'product_category_id' => $category->id,
        'name' => 'T-Shirt',
        'uucode' => 'TSHIRT-001',
    ]);

    $size = ProductSize::create([
        'product_id' => $product->id,
        'name' => 'Large',
        'code' => 'L',
    ]);

    $stock = Stock::create([
        'status' => 'draft',
        'notes' => 'Initial stock',
    ]);

    $stockItem = StockItem::create([
        'stock_id' => $stock->id,
        'product_id' => $product->id,
        'product_size_id' => $size->id,
        'quantity' => 10,
        'unit_price' => 2500,
    ]);

    $this->assertTrue((new LockStock())->execute($stock));
    $this->assertSame('locked', $stock->fresh()->status);
    $this->assertNotNull($stock->fresh()->locked_at);

    $price = ProductPrice::query()
        ->where('product_id', $product->id)
        ->where('product_size_id', $size->id)
        ->first();

    expect($price)->not->toBeNull()
        ->and((float) $price->price)->toBe(2500.0)
        ->and($price->product_id)->toBe($product->id)
        ->and($price->product_size_id)->toBe($size->id);
});

test('create stock items action can create and lock stock records', function () {
    $category = ProductCategory::create([
        'name' => 'Shoes',
        'slug' => 'shoes',
    ]);

    $product = Product::create([
        'product_category_id' => $category->id,
        'name' => 'Sneaker',
        'uucode' => 'SNEAKER-001',
    ]);

    $size = ProductSize::create([
        'product_id' => $product->id,
        'name' => 'Medium',
        'code' => 'M',
    ]);

    $stock = (new CreateStockItems())->execute([
        'status' => 'draft',
        'notes' => 'Action-based stock',
        'items' => [
            [
                'product_id' => $product->id,
                'product_size_id' => $size->id,
                'quantity' => 5,
                'unit_price' => 3000,
            ],
        ],
    ]);

    $locked = (new LockStock())->execute($stock);

    expect($locked)->toBeTrue()
        ->and($stock->reference)->toStartWith('STK-')
        ->and($stock->fresh()->status)->toBe('locked');

    $price = ProductPrice::query()
        ->where('product_id', $product->id)
        ->where('product_size_id', $size->id)
        ->first();

    expect($price)->not->toBeNull()
        ->and((float) $price->price)->toBe(3000.0);
});

test('create stock items action generates unique stock references internally', function () {
    $first = (new CreateStockItems())->execute([
        'notes' => 'First generated stock reference',
    ]);

    $second = (new CreateStockItems())->execute([
        'notes' => 'Second generated stock reference',
    ]);

    expect($first->reference)->toStartWith('STK-')
        ->and($second->reference)->toStartWith('STK-')
        ->and($first->reference)->not->toBe($second->reference)
        ->and(Stock::query()->pluck('reference')->unique())->toHaveCount(2);
});

test('locking a stock updates existing product prices', function () {
    $category = ProductCategory::create([
        'name' => 'Outerwear',
        'slug' => 'outerwear',
    ]);

    $product = Product::create([
        'product_category_id' => $category->id,
        'name' => 'Jacket',
        'uucode' => 'JACKET-001',
    ]);

    $size = ProductSize::create([
        'product_id' => $product->id,
        'name' => 'Extra Large',
        'code' => 'XL',
    ]);

    ProductPrice::create([
        'product_id' => $product->id,
        'product_size_id' => $size->id,
        'price' => 4000,
    ]);

    $stock = (new CreateStockItems())->execute([
        'items' => [
            [
                'product_id' => $product->id,
                'product_size_id' => $size->id,
                'quantity' => 2,
                'unit_price' => 4500,
            ],
        ],
    ]);

    expect((new LockStock())->execute($stock))->toBeTrue()
        ->and($stock->reference)->toStartWith('STK-')
        ->and(ProductPrice::query()->count())->toBe(1)
        ->and((float) ProductPrice::query()->first()->price)->toBe(4500.0);
});

test('locking an already locked stock is rejected', function () {
    $stock = Stock::create([
        'status' => 'locked',
        'locked_at' => now(),
    ]);

    expect(fn () => (new LockStock())->execute($stock))
        ->toThrow(InvalidArgumentException::class, 'Stock is already locked.');
});

test('batch actions can create multiple products and stock entries', function () {
    $category = ProductCategory::create([
        'name' => 'Accessories',
        'slug' => 'accessories',
    ]);

    $products = app(CreateProducts::class)->execute([
        [
            'product_category_id' => $category->id,
            'name' => 'Cap',
            'size' => 'Small',
            'is_active' => true,
        ],
        [
            'product_category_id' => $category->id,
            'name' => 'Watch',
            'size' => 'Small',
            'is_active' => true,
        ],
    ]);

    $stock = (new CreateStockItems())->execute([
        'status' => 'draft',
        'notes' => 'Bulk batch stock',
        'items' => [
            [
                'product_id' => $products[0]->id,
                'product_size_id' => $products[0]->resolved_product_size_id,
                'quantity' => 8,
                'unit_price' => 1800,
            ],
            [
                'product_id' => $products[1]->id,
                'product_size_id' => $products[1]->resolved_product_size_id,
                'quantity' => 4,
                'unit_price' => 2200,
            ],
        ],
    ]);

    expect($stock->items()->count())->toBe(2)
        ->and($stock->reference)->toStartWith('STK-')
        ->and($stock->fresh()->status)->toBe('draft');
});

test('create products action returns created products in request order', function () {
    $category = ProductCategory::create([
        'name' => 'Bags',
        'slug' => 'bags',
    ]);

    $products = app(CreateProducts::class)->execute([
        [
            'product_category_id' => $category->id,
            'name' => 'Tote',
        ],
        [
            'product_category_id' => $category->id,
            'name' => 'Satchel',
        ],
    ]);

    expect($products->pluck('name')->all())->toBe(['Tote', 'Satchel'])
        ->and($products[0]->uucode)->toStartWith('UUC-')
        ->and($products[1]->uucode)->toStartWith('UUC-')
        ->and($products[0]->uucode)->not->toBe($products[1]->uucode)
        ->and($products[0]->exists)->toBeTrue()
        ->and($products[1]->exists)->toBeTrue();
});

test('create products action creates multiple product sizes per product', function () {
    $category = ProductCategory::create([
        'name' => 'Clothing',
        'slug' => 'clothing',
    ]);

    $products = app(CreateProducts::class)->execute([
        [
            'product_category_id' => $category->id,
            'name' => 'Polo',
            'sizes' => ['S', 'M', 'L'],
        ],
    ]);

    expect($products[0]->resolved_product_sizes)->toHaveCount(3)
        ->and($products[0]->resolved_product_sizes->pluck('code')->all())->toBe(['S', 'M', 'L'])
        ->and(ProductSize::query()->where('product_id', $products[0]->id)->pluck('code')->all())->toBe(['S', 'M', 'L']);
});

test('create products action rolls back if one product cannot be created', function () {
    $category = ProductCategory::create([
        'name' => 'Belts',
        'slug' => 'belts',
    ]);

    expect(fn () => app(CreateProducts::class)->execute([
        [
            'product_category_id' => $category->id,
            'name' => 'Leather Belt',
        ],
        [
            'product_category_id' => 999999,
            'name' => 'Canvas Belt',
        ],
    ]))->toThrow(QueryException::class);

    expect(Product::query()->where('name', 'Leather Belt')->count())->toBe(0);
});

test('create products action inserts more than one hundred products in chunks', function () {
    $category = ProductCategory::create([
        'name' => 'Safety Products',
        'slug' => 'safety-products',
    ]);

    $products = array_map(static fn (int $index): array => [
        'product_category_id' => $category->id,
        'name' => 'Bulk Product '.$index,
    ], range(1, 101));

    $created = app(CreateProducts::class)->execute($products);

    expect($created)->toHaveCount(101)
        ->and($created->pluck('uucode')->unique())->toHaveCount(101)
        ->and(Product::query()->where('uucode', 'like', 'UUC-%')->count())->toBe(101);
});

test('create stock items action inserts more than one hundred stock items in chunks', function () {
    $category = ProductCategory::create([
        'name' => 'Safety Stock',
        'slug' => 'safety-stock',
    ]);

    $product = Product::create([
        'product_category_id' => $category->id,
        'name' => 'Safety Shoe',
        'uucode' => 'SAFETY-SHOE-001',
    ]);

    $size = ProductSize::create([
        'product_id' => $product->id,
        'name' => 'Universal',
        'code' => 'U',
    ]);

    $items = array_map(static fn (): array => [
        'product_id' => $product->id,
        'product_size_id' => $size->id,
        'quantity' => 1,
        'unit_price' => 1000,
    ], range(1, 101));

    $stock = (new CreateStockItems())->execute([
        'items' => $items,
    ]);

    expect($stock->items)->toHaveCount(101)
        ->and($stock->reference)->toStartWith('STK-')
        ->and(Stock::query()->where('reference', $stock->reference)->exists())->toBeTrue()
        ->and(StockItem::query()->where('stock_id', $stock->id)->count())->toBe(101);
});

test('create stock items action adds items to an existing open stock by id', function () {
    $category = ProductCategory::create([
        'name' => 'Open Stock',
        'slug' => 'open-stock',
    ]);

    $product = Product::create([
        'product_category_id' => $category->id,
        'name' => 'Open Product',
        'uucode' => 'OPEN-PRODUCT-001',
    ]);

    $size = ProductSize::create([
        'product_id' => $product->id,
        'name' => 'Pack',
        'code' => 'PACK',
    ]);

    $stock = Stock::create([
        'status' => 'draft',
    ]);

    $updated = (new CreateStockItems())->execute([
        'stock_id' => $stock->id,
        'items' => [
            [
                'product_id' => $product->id,
                'product_size_id' => $size->id,
                'quantity' => 3,
                'unit_price' => 1200,
            ],
        ],
    ]);

    expect($updated->id)->toBe($stock->id)
        ->and(Stock::query()->count())->toBe(1)
        ->and($updated->items)->toHaveCount(1)
        ->and($updated->items->first()->quantity)->toBe(3);
});

test('create stock items action rejects locked stocks', function () {
    $category = ProductCategory::create([
        'name' => 'Locked Stock',
        'slug' => 'locked-stock',
    ]);

    $product = Product::create([
        'product_category_id' => $category->id,
        'name' => 'Locked Product',
        'uucode' => 'LOCKED-PRODUCT-001',
    ]);

    $size = ProductSize::create([
        'product_id' => $product->id,
        'name' => 'Carton',
        'code' => 'CARTON',
    ]);

    $stock = Stock::create([
        'status' => 'locked',
        'locked_at' => now(),
    ]);

    expect(fn () => (new CreateStockItems())->execute([
        'stock_id' => $stock->id,
        'items' => [
            [
                'product_id' => $product->id,
                'product_size_id' => $size->id,
                'quantity' => 3,
                'unit_price' => 1200,
            ],
        ],
    ]))->toThrow(InvalidArgumentException::class, 'Cannot add items to a locked stock.');

    expect(StockItem::query()->count())->toBe(0);
});
