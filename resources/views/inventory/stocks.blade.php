@extends('inventory.layout')

@section('title', 'Stocks')

@section('content')
    <section class="page-head">
        <div>
            <div class="eyebrow">Stock control</div>
            <h1>Stocks and stock items</h1>
            <p class="summary">Each stock record is shown with its status, notes, lock date, item count, and line item values.</p>
        </div>
    </section>

    <section class="metric-row" aria-label="Stock summary">
        <div class="metric"><span>Total stocks</span><strong>{{ $stocks->count() }}</strong></div>
        <div class="metric"><span>Locked stocks</span><strong>{{ $stocks->whereNotNull('locked_at')->count() }}</strong></div>
        <div class="metric"><span>Stock items</span><strong>{{ $stocks->sum('items_count') }}</strong></div>
    </section>

    @if($stocks->isEmpty())
        <div class="empty-state">No stocks have been created yet.</div>
    @else
        <section class="grid">
            @foreach($stocks as $stock)
                @php
                    $total = $stock->items->sum(fn ($item) => (int) $item->quantity * (float) $item->unit_price);
                @endphp
                <article class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <div>
                                <h2>{{ $stock->reference }}</h2>
                                <div class="muted small">{{ $stock->created_at?->format('M j, Y') }}</div>
                            </div>
                            <span @class(['badge', 'dark' => $stock->locked_at, 'warn' => ! $stock->locked_at])>
                                {{ $stock->locked_at ? 'Locked' : ucfirst($stock->status) }}
                            </span>
                        </div>

                        <p class="small muted">{{ $stock->notes ?: 'No notes provided.' }}</p>

                        <div class="meta">
                            <span class="badge">{{ $stock->items_count }} items</span>
                            <span class="badge">₦{{ number_format($total, 2) }}</span>
                            @if($stock->locked_at)
                                <span class="badge">{{ $stock->locked_at->format('M j, Y') }}</span>
                            @endif
                        </div>

                        <div class="list">
                            @forelse($stock->items as $item)
                                <div class="line">
                                    <span>
                                        {{ $item->product?->name ?? 'Unknown product' }}
                                        <span class="muted">/ {{ $item->size?->name ?? 'No size' }}</span>
                                    </span>
                                    <span>{{ $item->quantity }} x ₦{{ number_format((float) $item->unit_price, 2) }}</span>
                                </div>
                            @empty
                                <div class="small muted">No stock items added yet.</div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
