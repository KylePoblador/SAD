@props([
    'title',
    'subtitle' => null,
    'wide' => false,
])

@php
    $inner = $wide
        ? 'mx-auto w-full max-w-full px-4 sm:px-6 md:px-8 lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl'
        : 'coinmeal-container-staff';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 pb-8 font-sans text-gray-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="{{ $inner }} py-3">
            <a href="{{ route('staff.dashboard') }}"
                class="mb-1 inline-flex items-center gap-1 text-sm font-medium text-green-600 hover:text-green-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <h1 class="text-lg font-bold text-gray-900">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-xs text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($tabs)
            <div class="border-t border-gray-100 bg-gray-50">
                <div class="{{ $inner }} py-2">
                    {{ $tabs }}
                </div>
            </div>
        @endisset
    </header>

    <main class="{{ $inner }} space-y-4 py-4">
        {{ $slot }}
    </main>
</body>

</html>
