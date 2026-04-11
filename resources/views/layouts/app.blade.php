<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'CoinMeal'))</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <main class="coinmeal-container py-4">
        @yield('content')
    </main>
</body>

</html>
