<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff dashboard — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body
    class="min-h-screen bg-gradient-to-b from-emerald-50/90 via-white to-sky-50/40 pb-24 font-sans text-gray-900 antialiased">

    <header class="sticky top-0 z-10 border-b border-emerald-100/80 bg-white/90 shadow-sm backdrop-blur-sm">
        <div class="coinmeal-container flex items-start justify-between gap-3 py-3">
        <div class="min-w-0">
            <h1 class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-lg font-bold text-transparent">
                {{ $staffCollegeName ?? 'Assigned canteen' }}</h1>
            <p class="text-xs font-medium text-gray-500">Staff dashboard</p>
            @if (!empty($collegeCode))
                <p class="mt-0.5 inline-block rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-emerald-800">{{ $collegeCode }}</p>
            @endif
        </div>
        <a href="{{ route('staff.notification') }}"
            class="relative flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span id="staff-unread-badge"
                class="absolute -right-1 -top-1 hidden h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">0</span>
        </a>
        </div>
    </header>

    <div class="coinmeal-container space-y-4 py-4 sm:space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div
                class="rounded-2xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm ring-1 ring-emerald-100/50">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                    <p class="text-xs font-semibold text-emerald-800/80">Today's orders</p>
                </div>
                <p class="text-2xl font-bold text-emerald-700">{{ $todayOrders ?? 0 }}</p>
            </div>
            <div
                class="rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm ring-1 ring-amber-100/50">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-xs font-semibold text-amber-900/70">Revenue</p>
                </div>
                <p class="text-2xl font-bold text-amber-700">₱{{ number_format($revenue ?? 0, 2) }}</p>
            </div>
            <div
                class="rounded-2xl border border-sky-200/60 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm ring-1 ring-sky-100/50">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <p class="text-xs font-semibold text-sky-900/70">Available seats</p>
                </div>
                <p class="text-2xl font-bold text-sky-700">{{ $availableSeats ?? 0 }}/{{ $totalSeats ?? 50 }}</p>
            </div>
            <div
                class="rounded-2xl border border-orange-200/60 bg-gradient-to-br from-orange-50 to-white p-4 shadow-sm ring-1 ring-orange-100/50">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </span>
                    <p class="text-xs font-semibold text-orange-900/70">Rating</p>
                </div>
                <p class="text-2xl font-bold text-orange-600">{{ $rating ?? '0.0' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <span class="h-1 w-8 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400"></span>
            <p class="text-base font-bold text-gray-800">Quick actions</p>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3">
            <a href="{{ route('staff.orders') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/30 p-4 text-center shadow-sm transition hover:border-emerald-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-emerald-900">Orders</span>
            </a>
            <a href="{{ route('staff.menu') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-teal-200/70 bg-gradient-to-br from-teal-50 via-white to-cyan-50/30 p-4 text-center shadow-sm transition hover:border-teal-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-teal-900">Menu</span>
            </a>
            <a href="{{ route('staff.wallet') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-yellow-50/30 p-4 text-center shadow-sm transition hover:border-amber-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-amber-900">Load wallet</span>
            </a>
            <a href="{{ route('staff.seats') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-sky-200/70 bg-gradient-to-br from-sky-50 via-white to-blue-50/30 p-4 text-center shadow-sm transition hover:border-sky-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-sky-900">Seats</span>
            </a>
            <a href="{{ route('staff.feedbacks') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-violet-200/70 bg-gradient-to-br from-violet-50 via-white to-fuchsia-50/30 p-4 text-center shadow-sm transition hover:border-violet-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-violet-900">Feedbacks</span>
            </a>
            <a href="{{ route('staff.qr.scanner') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50 via-white to-purple-50/30 p-4 text-center shadow-sm transition hover:border-indigo-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h3v3H7V7zm7 0h3v3h-3V7zM7 14h3v3H7v-3zm7 2h3m-3-2h3v5h-5v-3m-2 3H5V5h14v5" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-indigo-900">QR scanner</span>
            </a>
            <a href="{{ route('staff.reports') }}"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50 via-white to-slate-50/30 p-4 text-center shadow-sm transition hover:border-indigo-400 hover:shadow-md">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-md transition group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                <span class="text-sm font-bold text-indigo-900">Reports</span>
            </a>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <span class="h-1 w-8 rounded-full bg-gradient-to-r from-amber-400 to-orange-400"></span>
            <p class="text-base font-bold text-gray-800">Recent orders</p>
        </div>
        @forelse ($recentOrders ?? [] as $order)
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-900 ring-yellow-300/80',
                    'preparing' => 'bg-orange-100 text-orange-900 ring-orange-300/80',
                    'ready' => 'bg-green-100 text-green-800 ring-green-300/80',
                    'completed' => 'bg-green-600 text-white ring-green-700/80',
                ];
                $pill = $statusColors[strtolower($order->status ?? '')] ?? 'bg-violet-100 text-violet-900 ring-violet-200/80';
            @endphp
            <div
                class="flex items-center justify-between rounded-2xl border border-gray-100/80 bg-white/90 px-4 py-3 shadow-sm ring-1 ring-gray-100/60 backdrop-blur-sm">
                <span class="text-sm font-semibold text-gray-800">{{ $order->customer_name }}</span>
                <span
                    class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $pill }}">{{ strtoupper($order->status) }}</span>
            </div>
        @empty
            <div
                class="rounded-2xl border border-dashed border-emerald-200/80 bg-emerald-50/40 px-4 py-8 text-center text-sm text-emerald-800/70">
                No recent orders yet — they will show up here.</div>
        @endforelse
    </div>

    <nav
        class="fixed bottom-0 left-0 right-0 z-10 flex justify-around border-t border-emerald-100/90 bg-white/95 py-3 shadow-[0_-4px_20px_-4px_rgba(16,185,129,0.12)] backdrop-blur-md">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-xs font-semibold text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="mb-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Dashboard
        </a>
        <a href="{{ route('staff.orders') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="mb-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Orders
        </a>
        <a href="{{ route('staff.menu') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="mb-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Menu
        </a>
        <a href="{{ route('staff.profile') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="mb-0.5 h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
    </nav>

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
