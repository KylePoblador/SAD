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

<body
    class="min-h-screen bg-gray-100 pb-[max(7rem,calc(4.75rem+env(safe-area-inset-bottom,0px)))] font-sans text-gray-900 antialiased">
    @include('partials.student-topbar')

    @php
        $studentBackHref = match (request()->route()?->getName()) {
            'student.dashboard' => url('/'),
            'student.cart' => route('student.cart.hub'),
            'student.reserve' => route('student.canteen', ['college' => request()->route('college')]),
            default => route('student.dashboard'),
        };
    @endphp
    <div class="coinmeal-container pb-0 pt-2">
        @include('partials.app-back-link', ['href' => $studentBackHref, 'variant' => 'student'])
    </div>

    <div class="coinmeal-container space-y-4 py-4 sm:space-y-5 md:space-y-6">
        {{ $slot }}
    </div>

    @include('partials.student-bottom-nav', ['active' => $active])

    @stack('scripts')
</body>

</html>
