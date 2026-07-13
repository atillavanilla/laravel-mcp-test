@extends('inventory.layout')

@section('title', 'Categories')

@section('content')
    <section class="page-head">
        <div>
            <div class="eyebrow">Category directory</div>
            <h1>Product categories</h1>
            <p class="summary">A simple category list with descriptions, slugs, product counts, and the products attached to each category.</p>
        </div>
    </section>

    <section class="metric-row" aria-label="Category summary">
        <div class="metric"><span>Total categories</span><strong>{{ $categories->count() }}</strong></div>
        <div class="metric"><span>Assigned products</span><strong>{{ $categories->sum('products_count') }}</strong></div>
        <div class="metric"><span>Empty categories</span><strong>{{ $categories->where('products_count', 0)->count() }}</strong></div>
    </section>

    @if($categories->isEmpty())
        <div class="empty-state">No categories have been created yet.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                <div class="muted small">{{ $category->slug }}</div>
                            </td>
                            <td class="small muted">{{ $category->description ?: 'No description provided.' }}</td>
                            <td>
                                <span class="badge">{{ $category->products_count }} products</span>
                                <div class="small muted" style="margin-top: 8px;">
                                    {{ $category->products->take(4)->pluck('name')->join(', ') ?: 'No products assigned.' }}
                                    @if($category->products_count > 4)
                                        and {{ $category->products_count - 4 }} more
                                    @endif
                                </div>
                            </td>
                            <td class="small muted">{{ $category->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
