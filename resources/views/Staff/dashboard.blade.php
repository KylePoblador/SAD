<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: #f3f4f6;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 pb-20">

    {{-- Header --}}
    <div class="bg-green-500 px-4 py-4 text-white sticky top-0 z-10">
        <div class="flex items-center justify-between max-w-lg mx-auto">
            <div>
                <h1 class="text-lg font-bold">{{ $staffCollegeName ?? 'Assigned Canteen' }}</h1>
                <p class="text-xs opacity-85">Staff Dashboard</p>
                <p class="text-[10px] opacity-80 mt-0.5">{{ $collegeCode ?? '' }}</p>
            </div>
            <a href="{{ route('staff.notification') }}" class="text-white hover:text-green-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </a>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-4 space-y-4">

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-4 border-2 border-green-500">
                <p class="text-xs text-gray-500 mb-1">Today's Orders</p>
                <p class="text-2xl font-bold text-green-500">{{ $todayOrders ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border-2 border-yellow-400">
                <p class="text-xs text-gray-500 mb-1">Revenue</p>
                <p class="text-2xl font-bold text-yellow-500">₱{{ number_format($revenue ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border-2 border-blue-500">
                <p class="text-xs text-gray-500 mb-1">Available Seats</p>
                <p class="text-2xl font-bold text-blue-500">{{ $availableSeats ?? 0 }}/{{ $totalSeats ?? 50 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border-2 border-orange-400">
                <p class="text-xs text-gray-500 mb-1">Rating</p>
                <p class="text-2xl font-bold text-orange-400">{{ $rating ?? '0.0' }}</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <p class="text-base font-bold text-gray-800">Quick Actions</p>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('staff.orders') }}"
                class="bg-green-500 text-white text-center font-semibold py-4 rounded-xl text-sm">Orders</a>
            <a href="{{ route('staff.menu') }}"
                class="bg-yellow-400 text-yellow-900 text-center font-semibold py-4 rounded-xl text-sm">Menu</a>
            <a href="{{ route('staff.wallet') }}"
                class="bg-fuchsia-500 text-white text-center font-semibold py-4 rounded-xl text-sm">Load Wallet</a>
            <a href="{{ route('staff.seats') }}"
                class="bg-blue-500 text-white text-center font-semibold py-4 rounded-xl text-sm">Seats</a>
            <a href="{{ route('staff.feedbacks') }}"
                class="bg-pink-500 text-white text-center font-semibold py-4 rounded-xl text-sm">Feedbacks</a>
            <a href="{{ route('staff.reports') }}"
                class="bg-orange-500 text-white text-center font-semibold py-4 rounded-xl text-sm">Reports</a>
        </div>

        {{-- Recent Orders --}}
        <p class="text-base font-bold text-gray-800">Recent Orders</p>
        @forelse($recentOrders ?? [] as $order)
            <div class="bg-white rounded-xl px-4 py-3 shadow-sm flex items-center justify-between">
                <span class="text-sm font-medium text-gray-800">{{ $order->customer_name }}</span>
                <span
                    class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full">{{ strtoupper($order->status) }}</span>
            </div>
        @empty
            <div class="bg-white rounded-xl px-4 py-3 shadow-sm">
                <p class="text-sm text-gray-400">No recent orders.</p>
            </div>
        @endforelse

    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-3 z-10">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-xs text-green-600 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Dashboard
        </a>
        <a href="{{ route('staff.orders') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Orders
        </a>
        <a href="{{ route('staff.menu') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Menu
        </a>
        <a href="{{ route('staff.profile') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
    </div>

</body>

</html>
