<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoinMeal - Student Dashboard</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body { font-family: 'Figtree', sans-serif; background: #f3f4f6; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 pb-20">

    {{-- Top Header --}}
    <div class="bg-white px-4 py-3 flex items-center justify-between shadow-sm sticky top-0 z-10">
        <div>
            <h1 class="text-lg font-bold text-green-600">CoinMeal</h1>
            <p class="text-xs text-gray-500">University of Southern Mindanao</p>
        </div>
        <div class="flex items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-4 space-y-4">

        {{-- Wallet Balance Card --}}
        <div class="bg-green-500 rounded-2xl p-5 text-white relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium opacity-90">Available Balance</p>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-4xl font-bold mb-3">₱250.00</p>
            <div class="bg-green-400 rounded-xl px-4 py-2 text-xs opacity-90">
                To top up your wallet, visit any canteen counter and deposit cash.
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
                </svg>
                <div>
                    <p class="text-xs text-gray-500">Active Orders</p>
                    <p class="text-xl font-bold text-gray-800">2</p>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <div>
                    <p class="text-xs text-gray-500">Total Orders</p>
                    <p class="text-xl font-bold text-gray-800">5</p>
                </div>
            </div>
        </div>

        {{-- Active Order Banner --}}
        <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-xs text-orange-500 font-semibold">Active Order</p>
                <p class="text-sm text-gray-700 font-medium">ORD-1738828500234 • Ready for Pickup</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>

        {{-- Browse Canteens --}}
        <div>
            <h2 class="text-base font-bold text-gray-800 mb-3">Browse Canteens</h2>

            {{-- Filter Tabs --}}
            <div class="flex gap-2 overflow-x-auto pb-2 mb-3">
                <button class="px-4 py-1.5 rounded-full bg-green-500 text-white text-xs font-semibold whitespace-nowrap">All</button>
                <button class="px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-semibold whitespace-nowrap">CEIT</button>
                <button class="px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-semibold whitespace-nowrap">CASS</button>
                <button class="px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-semibold whitespace-nowrap">CHEFS</button>
                <button class="px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-semibold whitespace-nowrap">CBDEM</button>
                <button class="px-4 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 text-xs font-semibold whitespace-nowrap">CTI</button>
            </div>

            {{-- Canteen List --}}
            <div class="space-y-3">
                @foreach([
                    ['name' => 'CEIT Canteen',    'college' => 'CEIT',  'dist' => '50m',  'seats' => '12/25', 'full' => false, 'rating' => '4.5'],
                    ['name' => 'CASS Food Hub',   'college' => 'CASS',  'dist' => '120m', 'seats' => '5/20',  'full' => true,  'rating' => '4.2'],
                    ['name' => 'CHEFS Dining',    'college' => 'CHEFS', 'dist' => '200m', 'seats' => '18/30', 'full' => false, 'rating' => '4.8'],
                    ['name' => 'CBDEM Snack Bar', 'college' => 'CBDEM', 'dist' => '180m', 'seats' => '8/15',  'full' => true,  'rating' => '4.3'],
                    ['name' => 'CTI Canteen',     'college' => 'CTI',   'dist' => '20m',  'seats' => '10/25', 'full' => false, 'rating' => '4.1'],
                ] as $canteen)
                <div class="bg-white rounded-xl px-4 py-3 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $canteen['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $canteen['college'] }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z" />
                            </svg>
                            <span class="text-xs text-gray-400">{{ $canteen['dist'] }}</span>
                            <span class="text-xs font-medium {{ $canteen['full'] ? 'text-red-500' : 'text-green-600' }}">
                                ● {{ $canteen['seats'] }} Seats
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded-lg">★ {{ $canteen['rating'] }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-3 z-10">
        <a href="{{ route('student.dashboard') }}" class="flex flex-col items-center text-xs text-green-600 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Home
        </a>
        <a href="#" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Order
        </a>
        <a href="{{ route('student.profile') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
    </div>

</body>
</html>