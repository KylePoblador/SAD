<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CoinMeal - My Wallet</title>

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

        {{-- WALLET CARD --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg">

            <div class="relative z-10">
                <p class="text-sm font-medium opacity-90 mb-2">
                    Available Balance
                </p>

                <p class="text-5xl font-bold mb-4">
                    ₱{{ number_format($wallet['balance'], 2) }}
                </p>

                <div class="bg-white/20 rounded-xl px-4 py-3 backdrop-blur-sm">
                    <p class="text-xs opacity-90 mb-1">College</p>
                    <p class="text-sm font-semibold">{{ strtoupper($wallet['college']) }}</p>
                </div>
            </div>

        </div>

        {{-- QUICK STATS --}}
        <div class="grid grid-cols-2 gap-3">

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 mb-2">
                    Total Spent
                </p>
                <p class="text-2xl font-bold text-gray-800">
                    ₱{{ number_format($wallet['total_spent'], 2) }}
                </p>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 mb-2">
                    Total Orders
                </p>
                <p class="text-2xl font-bold text-gray-800">
                    {{ $wallet['total_orders'] }}
                </p>
            </div>

        </div>

        {{-- WALLET ACTIONS --}}
        <div class="space-y-3">
            <button class="w-full bg-green-500 text-white font-semibold py-3 rounded-xl hover:bg-green-600 transition shadow-sm">
                Top Up Wallet
            </button>

            <button class="w-full bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl hover:bg-gray-300 transition shadow-sm">
                View Transactions
            </button>
        </div>

        {{-- RECENT TRANSACTIONS --}}
        <div>
            <h2 class="text-base font-bold text-gray-800 mb-3">
                Recent Transactions
            </h2>

            @forelse($wallet['recent_transactions'] as $transaction)
            <div class="bg-white rounded-xl p-4 shadow-sm mb-3">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $transaction['description'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $transaction['date'] }}</p>
                    </div>
                    <p class="font-bold text-lg {{ $transaction['type'] === 'debit' ? 'text-red-500' : 'text-green-500' }}">
                        {{ $transaction['type'] === 'debit' ? '-' : '+' }}₱{{ number_format($transaction['amount'], 2) }}
                    </p>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl p-8 text-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 text-sm">No transactions yet</p>
                <p class="text-gray-400 text-xs mt-1">Your transactions will appear here</p>
            </div>
            @endforelse

        </div>

    </div>



    {{-- BOTTOM NAV --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-3">

        <a href="{{ route('student.dashboard') }}"
            class="flex flex-col items-center text-xs text-gray-400">

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
                    }
                } catch (error) {
                    console.error('Error updating unread count:', error);
                }
        }

        // Update count on page load
        updateUnreadCount();

        // Update count every 30 seconds
        setInterval(updateUnreadCount, 30000);
    </script>

</body>

</html>
