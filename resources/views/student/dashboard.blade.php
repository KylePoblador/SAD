<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CoinMeal - Student Dashboard</title>

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


    {{-- HEADER --}}
    <div class="bg-white px-4 py-3 flex items-center justify-between shadow-sm sticky top-0 z-10">

        <div>
            <h1 class="text-lg font-bold text-green-600">
                CoinMeal
            </h1>

            <p class="text-xs text-gray-500">
                University of Southern Mindanao
            </p>
        </div>


        <div class="flex items-center gap-4">

            {{-- notification --}}
            <a href="{{ route('student.notification') }}" class="text-gray-500 hover:text-green-600 relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032
2.032 0 0118 14.158V11a6 6
0 10-12 0v3.159c0 .538-.214
1.055-.595 1.436L4 17h5m6
0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="unread-badge"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold text-[10px]"
                    style="display: none;">0</span>
            </a>


            {{-- cart --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4
M7 13l-1.5 6h13M7 13L5.4 5M10
21a1 1 0 100-2 1 1 0 000
2zm7 0a1 1 0 100-2 1 1 0
000 2z" />

            </svg>

        </div>

    </div>



    <div class="max-w-lg mx-auto px-4 py-4 space-y-4">


        {{-- WALLET --}}
        <div class="block">
            <div class="bg-green-500 rounded-2xl p-5 text-white relative overflow-hidden">

                <div class="flex items-center justify-between mb-2">

                    <p class="text-sm font-medium opacity-90">
                        Available Balance
                    </p>

                </div>

                <p class="text-4xl font-bold mb-3">
                    ₱{{ number_format($walletBalance, 2) }}
                </p>


                <div class="bg-green-400 rounded-xl px-4 py-2 text-xs opacity-90">

                To top up your wallet,
                visit any canteen counter
                and deposit cash.

            </div>

        </div>
        </div>



        {{-- STATS --}}
        <div class="grid grid-cols-2 gap-3">

            <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">

                <div>
                    <p class="text-xs text-gray-500">
                        Active Orders
                    </p>

                    <p class="text-xl font-bold text-gray-800">
                        2
                    </p>
                </div>

            </div>



            <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">

                <div>
                    <p class="text-xs text-gray-500">
                        Total Orders
                    </p>

                    <p class="text-xl font-bold text-gray-800">
                        5
                    </p>
                </div>

            </div>

        </div>



        {{-- ACTIVE ORDER --}}
        <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 flex items-center justify-between">

            <div>

                <p class="text-xs text-orange-500 font-semibold">
                    Active Order
                </p>

                <p class="text-sm text-gray-700 font-medium">

                    ORD-1738828500234
                    • Ready for Pickup

                </p>

            </div>

        </div>



        {{-- CANTEENS --}}
        <div>

            <h2 class="text-base font-bold text-gray-800 mb-3">

                Browse Canteens

            </h2>



            <div class="space-y-3">

                @foreach ($canteens as $canteen)
                    <a href="{{ route('student.canteen', $canteen['college']) }}"
                        class="bg-white rounded-xl px-4 py-3 shadow-sm
flex items-center justify-between
hover:bg-gray-50 transition">

                        <div>

                            <p class="font-semibold text-gray-800 text-sm">

                                {{ $canteen['name'] }}

                            </p>


                            <p class="text-xs text-gray-400">

                                {{ strtoupper($canteen['college']) }}

                            </p>



                            <div class="flex items-center gap-2 mt-1">

                                <span class="text-xs text-gray-400">

                                    {{ $canteen['dist'] }}

                                </span>


                                <span
                                    class="text-xs font-medium {{ $canteen['full'] ? 'text-red-500' : 'text-green-600' }}">

                                    ● {{ $canteen['seats'] }} Seats

                                </span>

                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                Staff: {{ $canteen['staff_names'] }}
                            </p>

                        </div>



                        <div>

                            <span class="bg-yellow-100
text-yellow-700 text-xs font-bold
px-2 py-1 rounded-lg">

                                ★ {{ $canteen['rating'] }}

                            </span>

                        </div>


                    </a>
                @endforeach


            </div>

        </div>

    </div>



    {{-- BOTTOM NAV --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-3">

        <a href="{{ route('student.dashboard') }}"
            class="flex flex-col items-center text-xs text-green-600 font-semibold">

            Home

        </a>


        <a href="{{ route('student.orders') }}" class="flex flex-col items-center text-xs text-gray-400">

            Orders

        </a>


        <a href="{{ route('student.profile') }}" class="flex flex-col items-center text-xs text-gray-400">

            Profile

        </a>

    </div>

    <script>
        // Update unread notification count
        const unreadBadge = document.getElementById('unread-badge');
        const unreadCountEndpoint = '{{ route('student.unread-count') }}';

        async function updateUnreadCount() {
                try {
                    const response = await fetch(unreadCountEndpoint, {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });
                    const data = await response.json();
                    const count = data.unread_count || 0;

                    if (count > 0) {
                        unreadBadge.textContent = count > 99 ? '99+' : count;
                        unreadBadge.style.display = 'flex';
                    } else {
                        unreadBadge.style.display = 'none';

                        <
                        /html>
