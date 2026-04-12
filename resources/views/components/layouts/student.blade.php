@props([
    'title' => 'CoinMeal',
    'active' => 'home',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 pb-24 font-sans text-gray-900 antialiased">
    @include('partials.student-topbar')

    <div class="coinmeal-container space-y-4 py-4 sm:space-y-5 md:space-y-6">
        {{ $slot }}
    </div>

    @include('partials.student-bottom-nav', ['active' => $active])

    @stack('scripts')
</body>

</html>
