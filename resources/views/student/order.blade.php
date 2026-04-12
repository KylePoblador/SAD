<x-layouts.student title="My orders" active="orders">
    <div class="relative overflow-hidden rounded-2xl bg-green-600 p-5 text-white shadow-sm">
        <div class="mb-2 flex items-center justify-between">
            <p class="text-sm font-medium opacity-90">Total balance (all canteens)</p>
        </div>
        <p class="mb-3 text-4xl font-bold">₱{{ number_format($walletBalance, 2) }}</p>
        <div class="rounded-xl bg-green-500/90 px-4 py-3 text-xs opacity-95">
            <p class="mb-2">Per-canteen balances show when you open that canteen. To add funds, use <strong>Load wallet</strong> on the Wallet page.</p>
            <a href="{{ route('student.wallet') }}"
                class="inline-flex w-full items-center justify-center rounded-lg bg-white/95 px-3 py-2 font-semibold text-green-700 shadow-sm hover:bg-white sm:w-auto">
                Go to Wallet
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs text-gray-500">Active orders</p>
                <p class="text-xl font-bold text-gray-800">{{ $activeOrdersCount }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs text-gray-500">Total orders</p>
                <p class="text-xl font-bold text-gray-800">{{ $totalOrdersCount }}</p>
            </div>
        </div>
    </div>

    <div class="orders-container space-y-4">
        <h2 class="text-base font-bold text-gray-800">My orders</h2>

        <div class="mb-2 flex flex-wrap gap-2">
            <button type="button" id="tab-all"
                class="tab-button rounded-full bg-green-600 px-4 py-2 text-sm font-medium text-white"
                data-filter="all">All</button>
            <button type="button" id="tab-pending"
                class="tab-button rounded-full bg-gray-200 px-4 py-2 text-sm font-medium text-gray-600"
                data-filter="pending">Pending</button>
            <button type="button" id="tab-completed"
                class="tab-button rounded-full bg-gray-200 px-4 py-2 text-sm font-medium text-gray-600"
                data-filter="completed">Completed</button>
        </div>

        @forelse ($orders as $order)
            <div class="order-card mb-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
                data-status="{{ $order->status == 'completed' ? 'completed' : 'pending' }}">
                <div class="mb-2 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $order->order_number ?? 'ORD-' . $order->id }}</p>
                        <p class="text-xs text-gray-500">{{ $order->canteen }}</p>
                        <p class="text-xs text-gray-400">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    @if ($order->status == 'ready')
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Ready for
                            pickup</span>
                    @elseif($order->status == 'preparing')
                        <span
                            class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">Preparing</span>
                    @elseif($order->status == 'completed')
                        <span
                            class="rounded-full bg-green-600 px-3 py-1 text-xs font-semibold text-white">Completed</span>
                    @else
                        <span
                            class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-900">Pending</span>
                    @endif
                </div>

                <hr class="my-3 border-gray-100">

                @forelse ($order->items ?? [] as $item)
                    <div class="flex justify-between py-1 text-sm">
                        <span class="text-gray-700">{{ $item->qty }}× {{ $item->name }}</span>
                        <span class="text-gray-600">₱{{ number_format($item->price, 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">No line items recorded for this order.</p>
                @endforelse

                <hr class="my-3 border-gray-100">

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-lg font-bold text-green-600">₱{{ number_format($order->total, 2) }}</span>
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                            class="rounded-full bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200">
                            View receipt
                        </button>
                        @if ($order->status == 'ready')
                            <button type="button"
                                class="rounded-full bg-green-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-green-700">
                                Pick up
                            </button>
                        @elseif($order->status == 'completed')
                            <button type="button"
                                class="rounded-full bg-amber-400 px-3 py-2 text-xs font-medium text-amber-950 transition hover:bg-amber-500">
                                Rate order
                            </button>
                        @else
                            <button type="button"
                                class="rounded-full bg-green-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-green-700">
                                Track order
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state rounded-xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-sm text-gray-500">No orders yet</p>
                <p class="mt-1 text-xs text-gray-400">Your order history will appear here</p>
            </div>
        @endforelse
    </div>

    @push('scripts')
        <script>
            const unreadBadge = document.getElementById('unread-badge');
            const unreadCountEndpoint = @json(route('student.unread-count'));

            async function updateUnreadCount() {
                if (!unreadBadge) return;
                try {
                    const response = await fetch(unreadCountEndpoint, {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });
                    const data = await response.json();
                    const count = data.unread_count || 0;
                    if (count > 0) {
                        unreadBadge.textContent = count > 99 ? '99+' : String(count);
                        unreadBadge.style.display = 'flex';
                    } else {
                        unreadBadge.style.display = 'none';
                    }
                } catch (e) {}
            }
            updateUnreadCount();
            setInterval(updateUnreadCount, 30000);

            document.addEventListener('DOMContentLoaded', function() {
                const tabButtons = document.querySelectorAll('.tab-button');
                const orderCards = document.querySelectorAll('.order-card');

                tabButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        tabButtons.forEach(btn => {
                            btn.classList.remove('bg-green-600', 'text-white');
                            btn.classList.add('bg-gray-200', 'text-gray-600');
                        });
                        this.classList.add('bg-green-600', 'text-white');
                        this.classList.remove('bg-gray-200', 'text-gray-600');

                        const filter = this.getAttribute('data-filter');
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

                        const visibleCards = Array.from(orderCards).filter(card => card.style.display !== 'none');
                        let emptyState = document.querySelector('.empty-state');
                        const ordersContainer = document.querySelector('.orders-container');

                        if (visibleCards.length === 0 && orderCards.length > 0 && ordersContainer) {
                            if (!emptyState) {
                                emptyState = document.createElement('div');
                                emptyState.className =
                                    'empty-state rounded-xl border border-gray-100 bg-white p-8 text-center shadow-sm';
                                emptyState.innerHTML = `
                                    <p class="text-sm text-gray-500">No ${filter} orders</p>
                                    <p class="mt-1 text-xs text-gray-400">Try another tab</p>
                                `;
                                ordersContainer.appendChild(emptyState);
                            }
                        } else if (emptyState && emptyState.parentNode && visibleCards.length > 0 &&
                            emptyState.textContent.includes('No ') && emptyState.textContent.includes('orders')) {
                            emptyState.remove();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-layouts.student>
