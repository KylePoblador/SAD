<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitle = isset($title) && trim((string) $title) !== ''
            ? trim((string) $title)
            : trim((string) View::yieldContent('title', config('app.name', 'CoinMeal')));
    @endphp
    <title>{{ $pageTitle }}</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="min-h-screen">
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @elseif(View::hasSection('header'))
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @yield('header')
                </div>
            </header>
        @endif

        <main class="coinmeal-container py-4">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>
</body>

</html>
