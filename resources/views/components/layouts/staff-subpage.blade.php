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
        <div class="{{ $inner }} flex items-start justify-between gap-3 py-3">
            <div class="min-w-0">
                <div class="mb-1">
                    @include('partials.app-back-link', ['href' => route('staff.dashboard'), 'variant' => 'staff'])
                </div>
                <h1 class="text-lg font-bold text-gray-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-xs text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            <a href="{{ route('staff.notification') }}"
                class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                aria-label="Open notifications">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="staff-unread-badge"
                    class="absolute -right-1 -top-1 hidden h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">0</span>
            </a>
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

    <script>
        (function() {
            const badge = document.getElementById('staff-unread-badge');
            const endpoint = @json(route('staff.unread-count'));
            if (!badge || !endpoint) return;

            async function updateStaffUnreadBadge() {
                try {
                    const response = await fetch(endpoint, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                        },
                    });
                    const data = await response.json();
                    const count = Number(data.unread_count || 0);
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : String(count);
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                } catch (e) {
                    // Ignore badge polling errors.
                }
            }

            updateStaffUnreadBadge();
            setInterval(updateStaffUnreadBadge, 5000);
        })();
    </script>
</body>

</html>
