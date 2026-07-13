<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory') - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            color: #17211b;
            background: #f6f7f2;
        }
        a { color: inherit; text-decoration: none; }
        .shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 48px; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 30px;
        }
        .brand { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #17211b;
            color: #fff;
            font-weight: 800;
        }
        .brand strong { display: block; font-size: 1rem; }
        .brand span { display: block; color: #6c756d; font-size: .85rem; margin-top: 2px; }
        .nav { display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
        .nav a, .logout-button {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 0 13px;
            border: 1px solid #dfe4da;
            border-radius: 8px;
            background: #fff;
            color: #354137;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
        }
        .nav a.active { background: #17211b; border-color: #17211b; color: #fff; }
        .logout-form { margin: 0; }
        .page-head {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: end;
            gap: 20px;
            margin-bottom: 22px;
        }
        .eyebrow { color: #667060; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
        h1 { margin: 6px 0 0; font-size: clamp(1.8rem, 4vw, 3rem); line-height: 1.05; letter-spacing: 0; }
        .summary { color: #5f6a60; max-width: 620px; margin: 10px 0 0; line-height: 1.6; }
        .metric-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 22px; }
        .metric, .empty-state {
            border: 1px solid #dfe4da;
            border-radius: 8px;
            background: #fff;
            padding: 16px;
            box-shadow: 0 12px 26px rgba(23, 33, 27, .05);
        }
        .metric span { color: #69736b; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        .metric strong { display: block; margin-top: 6px; font-size: 1.8rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
        .card {
            border: 1px solid #dfe4da;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 12px 26px rgba(23, 33, 27, .05);
        }
        .card-body { padding: 18px; }
        .card-title { display: flex; align-items: start; justify-content: space-between; gap: 14px; margin-bottom: 12px; }
        .card h2 { margin: 0; font-size: 1.08rem; line-height: 1.3; }
        .muted { color: #69736b; }
        .small { font-size: .86rem; line-height: 1.5; }
        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            background: #edf3e8;
            color: #31543a;
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge.dark { background: #17211b; color: #fff; }
        .badge.warn { background: #fff3cf; color: #73530b; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0 0; }
        .list { border-top: 1px solid #edf0ea; margin-top: 16px; padding-top: 12px; display: grid; gap: 10px; }
        .line { display: flex; justify-content: space-between; gap: 12px; color: #354137; font-size: .88rem; }
        .line span:last-child { text-align: right; font-weight: 700; }
        .table-wrap { overflow-x: auto; border: 1px solid #dfe4da; border-radius: 8px; background: #fff; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        th, td { padding: 14px 16px; border-bottom: 1px solid #edf0ea; text-align: left; vertical-align: top; }
        th { color: #667060; font-size: .76rem; text-transform: uppercase; letter-spacing: .06em; }
        tr:last-child td { border-bottom: 0; }
        @media (max-width: 760px) {
            .topbar, .page-head { align-items: stretch; grid-template-columns: 1fr; flex-direction: column; }
            .nav { justify-content: flex-start; }
            .metric-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="{{ route('products.index') }}" class="brand">
                <span class="mark">I</span>
                <span>
                    <strong>{{ config('app.name', 'Inventory') }}</strong>
                    <span>{{ auth()->user()->name ?? auth()->user()->email }}</span>
                </span>
            </a>
            <nav class="nav" aria-label="Inventory navigation">
                <a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.index')])>Products</a>
                <a href="{{ route('stocks.index') }}" @class(['active' => request()->routeIs('stocks.index')])>Stocks</a>
                <a href="{{ route('categories.index') }}" @class(['active' => request()->routeIs('categories.index')])>Categories</a>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-button">Logout</button>
                </form>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
