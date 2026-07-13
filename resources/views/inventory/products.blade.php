@extends('inventory.layout')

@section('title', 'Products')

@section('content')
    <section class="page-head">
        <div>
            <div class="eyebrow">Product catalogue</div>
            <h1>Products in detail</h1>
            <p class="summary">A clear view of every product, its category, available sizes, prices, and stock item activity.</p>
        </div>
    </section>

    <section class="metric-row" aria-label="Product summary">
        <div class="metric"><span>Total products</span><strong>{{ $products->count() }}</strong></div>
        <div class="metric"><span>Active products</span><strong>{{ $products->where('is_active', true)->count() }}</strong></div>
        <div class="metric"><span>Categories used</span><strong>{{ $products->pluck('product_category_id')->filter()->unique()->count() }}</strong></div>
    </section>

    @if($products->isEmpty())
        <div class="empty-state">No products have been created yet.</div>
    @else
        <section class="grid">
            @foreach($products as $product)
                <article class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <div>
                                <h2>{{ $product->name }}</h2>
                                <div class="muted small">{{ $product->uucode }}</div>
                            </div>
                            <span @class(['badge', 'dark' => $product->is_active, 'warn' => ! $product->is_active])>
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <p class="small muted">{{ $product->description ?: 'No description provided.' }}</p>

                        <div class="meta">
                            <span class="badge">{{ $product->category?->name ?? 'No category' }}</span>
                            <span class="badge">{{ $product->sizes->count() }} sizes</span>
                            <span class="badge">{{ $product->stock_items_count }} stock items</span>
                        </div>

                        <div class="list">
                            @forelse($product->sizes as $size)
                                @php
                                    $price = $product->prices->firstWhere('product_size_id', $size->id);
                                @endphp
                                <div class="line">
                                    <span>{{ $size->name }} <span class="muted">({{ $size->code }})</span></span>
                                    <span>{{ $price ? '₦'.number_format((float) $price->price, 2) : 'No price' }}</span>
                                </div>
                            @empty
                                <div class="small muted">No sizes added yet.</div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
