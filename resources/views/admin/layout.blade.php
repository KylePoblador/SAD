<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>
<body class="bg-gray-50 text-gray-900">
<div class="mx-auto min-h-screen max-w-7xl p-4 md:p-6">
    <header class="mb-6 rounded-2xl bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">CoinMeal Admin</p>
                <h1 class="text-2xl font-bold">@yield('heading', 'Admin')</h1>
            </div>
            <nav class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-lg px-3 py-1.5 font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Dashboard</a>
                <a href="{{ route('admin.transactions') }}"
                   class="rounded-lg px-3 py-1.5 font-medium {{ request()->routeIs('admin.transactions') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Transactions</a>
                <a href="{{ route('admin.users') }}"
                   class="rounded-lg px-3 py-1.5 font-medium {{ request()->routeIs('admin.users*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Users</a>
                <a href="{{ route('admin.refunds') }}"
                   class="rounded-lg px-3 py-1.5 font-medium {{ request()->routeIs('admin.refunds') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Refunds</a>
                <a href="{{ route('admin.coupons') }}"
                   class="rounded-lg px-3 py-1.5 font-medium {{ request()->routeIs('admin.coupons*') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Coupons</a>
                <a href="{{ route('force-logout') }}" class="rounded-lg px-3 py-1.5 font-medium text-gray-500 hover:bg-gray-100">Logout</a>
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>
</body>
</html>
