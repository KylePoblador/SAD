<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CoinMeal - My Orders</title>

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
        <div class="bg-green-500 rounded-2xl p-5 text-white relative overflow-hidden">

            <div class="flex items-center justify-between mb-2">

                <p class="text-sm font-medium opacity-90">
                    Available Balance
                </p>

            </div>

            <p class="text-4xl font-bold mb-3">
                ₱250.00
            </p>


            <div class="bg-green-400 rounded-xl px-4 py-2 text-xs opacity-90">

                To top up your wallet,
                visit any canteen counter
                and deposit cash.

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
                        {{ $orders->where('status', '!=', 'completed')->count() }}
                    </p>
                </div>

            </div>



            <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm">

                <div>
                    <p class="text-xs text-gray-500">
                        Total Orders
                    </p>

                    <p class="text-xl font-bold text-gray-800">
                        {{ $orders->count() }}
                    </p>
                </div>

            </div>

        </div>



        {{-- ORDERS SECTION --}}
        <div class="orders-container">

            <h2 class="text-base font-bold text-gray-800 mb-3">

                My Orders

            </h2>

            {{-- Tabs --}}
            <div class="flex gap-2 mb-4">
                <button id="tab-all" class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-medium tab-button active" data-filter="all">All</button>
                <button id="tab-pending" class="bg-gray-200 text-gray-600 px-4 py-2 rounded-full text-sm font-medium tab-button" data-filter="pending">Pending</button>
                <button id="tab-completed" class="bg-gray-200 text-gray-600 px-4 py-2 rounded-full text-sm font-medium tab-button" data-filter="completed">Completed</button>
            </div>

            {{-- Order Cards --}}
            @forelse($orders as $order)
            <div class="bg-white rounded-xl shadow-sm p-4 mb-3 order-card" data-status="{{ $order->status == 'completed' ? 'completed' : 'pending' }}">

                {{-- Header --}}
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">ORD-{{ $order->id }}</p>
                        <p class="text-xs text-gray-500">{{ $order->canteen }}</p>
                        <p class="text-xs text-gray-400">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>

                    {{-- Status --}}
                    @if($order->status == 'ready')
                        <span class="bg-green-100 text-green-600 text-xs px-3 py-1 rounded-full font-medium">
                            Ready for Pickup
                        </span>
                    @elseif($order->status == 'preparing')
                        <span class="bg-orange-100 text-orange-600 text-xs px-3 py-1 rounded-full font-medium">
                            Preparing
                        </span>
                    @else
                        <span class="bg-gray-200 text-gray-600 text-xs px-3 py-1 rounded-full font-medium">
                            Completed
                        </span>
                    @endif
                </div>

                <hr class="my-3">

                {{-- Items --}}
                @foreach($order->items as $item)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-gray-700">{{ $item->qty }}x {{ $item->name }}</span>
                    <span class="text-gray-600">₱{{ number_format($item->price, 2) }}</span>
                </div>
                @endforeach

                <hr class="my-3">

                {{-- Footer --}}
                <div class="flex justify-between items-center">
                    <span class="text-green-600 font-bold text-lg">
                        ₱{{ number_format($order->total, 2) }}
                    </span>

                    <div class="flex gap-2">
                        <button class="bg-gray-200 text-gray-600 text-xs px-3 py-2 rounded-full font-medium hover:bg-gray-300 transition">
                            View Receipt
                        </button>

                        @if($order->status == 'ready')
                            <button class="bg-green-500 text-white text-xs px-3 py-2 rounded-full font-medium hover:bg-green-600 transition">
                                Pick up
                            </button>
                        @elseif($order->status == 'completed')
                            <button class="bg-yellow-400 text-white text-xs px-3 py-2 rounded-full font-medium hover:bg-yellow-500 transition">
                                Rate Order
                            </button>
                        @else
                            <button class="bg-green-500 text-white text-xs px-3 py-2 rounded-full font-medium hover:bg-green-600 transition">
                                Track Order
                            </button>
                        @endif
                    </div>
                </div>

            </div>
            @empty
            <div class="bg-white rounded-xl p-8 text-center shadow-sm empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-gray-500 text-sm">No orders yet</p>
                <p class="text-gray-400 text-xs mt-1">Your order history will appear here</p>
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


        <a href="{{ route('student.orders') }}" class="flex flex-col items-center text-xs text-green-600 font-semibold">

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

        // Tab filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const orderCards = document.querySelectorAll('.order-card');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'bg-green-500', 'text-white');
                        btn.classList.add('bg-gray-200', 'text-gray-600');
                    });

                    // Add active class to clicked button
                    this.classList.add('active', 'bg-green-500', 'text-white');
                    this.classList.remove('bg-gray-200', 'text-gray-600');

                    // Get filter type
                    const filter = this.getAttribute('data-filter');

                    // Filter orders
                    orderCards.forEach(card => {
                        const status = card.getAttribute('data-status');

                        if (filter === 'all') {
                            card.style.display = 'block';
                        } else if (filter === 'pending') {
                            card.style.display = status === 'pending' ? 'block' : 'none';
                        } else if (filter === 'completed') {
                            card.style.display = status === 'completed' ? 'block' : 'none';
                        }
                    });

                    // Handle empty state
                    const visibleCards = Array.from(orderCards).filter(card => card.style.display !== 'none');
                    const emptyState = document.querySelector('.empty-state');

                    if (visibleCards.length === 0 && !emptyState) {
                        // Create empty state if no cards are visible
                        const ordersContainer = document.querySelector('.orders-container');
                        const emptyDiv = document.createElement('div');
                        emptyDiv.className = 'bg-white rounded-xl p-8 text-center shadow-sm empty-state';
                        emptyDiv.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-gray-500 text-sm">No ${filter} orders</p>
                            <p class="text-gray-400 text-xs mt-1">Your ${filter} order history will appear here</p>
                        `;
                        ordersContainer.appendChild(emptyDiv);
                    } else if (emptyState && visibleCards.length > 0) {
                        // Remove empty state if cards become visible
                        emptyState.remove();
                    }
                });
            });
        });
    </script>

</body>

</html>
