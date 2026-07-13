<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stock;
use Illuminate\Http\Request;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/chat', ChatController::class)->name('chat');

Route::middleware('auth')->group(function () {
    Route::redirect('/dashboard', '/products')->name('dashboard');

    Route::get('/products', function () {
        $products = Product::query()
            ->with(['category', 'sizes' => fn ($query) => $query->orderBy('sort_order'), 'prices.size'])
            ->withCount('stockItems')
            ->latest()
            ->get();

        return view('inventory.products', compact('products'));
    })->name('products.index');

    Route::get('/stocks', function () {
        $stocks = Stock::query()
            ->with(['items.product', 'items.size'])
            ->withCount('items')
            ->latest()
            ->get();

        return view('inventory.stocks', compact('stocks'));
    })->name('stocks.index');

    Route::get('/categories', function () {
        $categories = ProductCategory::query()
            ->with(['products' => fn ($query) => $query->latest()])
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('inventory.categories', compact('categories'));
    })->name('categories.index');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
    
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');
