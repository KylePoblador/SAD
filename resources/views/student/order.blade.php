<x-layouts.student title="My orders" active="orders">
    @if (session('status') === 'order-placed')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
            <p class="font-semibold">Order placed for {{ session('order_placed_canteen', 'this canteen') }} only.</p>
            <p class="mt-1 text-xs leading-relaxed text-green-800/95">Each checkout creates a <strong>separate</strong> order and receipt per canteen. Items in your other canteen carts were not included.</p>
            @if (session('order_placed_id'))
                <a href="{{ route('student.orders.receipt', session('order_placed_id')) }}"
                    class="mt-3 inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-green-700 sm:w-auto">
                    View receipt for this order
                </a>
            @endif
        </div>
    @endif

    @if (session('status') && session('status') !== 'order-placed')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-900">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            {{ $errors->first() }}
        </div>
    @endif

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
        <div>
            <h2 class="text-base font-bold text-gray-800">My orders</h2>
            <p class="mt-0.5 text-[11px] text-gray-500">Every row is one canteen order — separate from orders at other canteens.</p>
        </div>

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

                @if ($order->status === 'completed' && $order->feedback)
                    <div class="mb-3 rounded-lg border border-amber-100 bg-amber-50/80 px-3 py-2 text-sm">
                        <p class="text-xs font-semibold text-amber-900">Your rating: {{ $order->feedback->rating }}/5</p>
                        @if ($order->feedback->comment)
                            <p class="mt-1 text-xs text-amber-900/90">{{ $order->feedback->comment }}</p>
                        @endif
                        @if ($order->feedback->staff_reply)
                            <div class="mt-2 border-t border-amber-200/80 pt-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Canteen reply</p>
                                <p class="mt-0.5 text-xs text-gray-800">{{ $order->feedback->staff_reply }}</p>
                                @if ($order->feedback->staff_reply_at)
                                    <p class="mt-1 text-[10px] text-gray-400">{{ $order->feedback->staff_reply_at->format('M d, Y g:i A') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-lg font-bold text-green-600">₱{{ number_format($order->total, 2) }}</span>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('student.orders.receipt', $order) }}"
                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-2 text-xs font-medium text-gray-800 transition hover:bg-gray-200">
                            View receipt
                        </a>
                        @if ($order->status == 'ready')
                            <span
                                class="rounded-full bg-green-600 px-3 py-2 text-xs font-medium text-white">Pick up at counter</span>
                        @elseif($order->status == 'completed' && ! $order->feedback)
                            <button type="button"
                                class="rate-order-btn rounded-full bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"
                                data-order-id="{{ $order->id }}"
                                data-order-label="{{ $order->order_number ?? 'ORD-' . $order->id }}">
                                Rate & feedback
                            </button>
                        @elseif($order->status !== 'completed')
                            <span class="rounded-full bg-gray-100 px-3 py-2 text-xs font-medium text-gray-600">Tracking</span>
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

    {{-- Rate modal --}}
    <div id="rate-modal" class="fixed inset-0 z-[80] hidden items-end justify-center bg-black/45 p-0 sm:items-center sm:p-4"
        role="dialog" aria-modal="true" aria-labelledby="rate-modal-title">
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:rounded-2xl">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 id="rate-modal-title" class="text-lg font-bold text-gray-900">Rate your order</h2>
                <p id="rate-modal-sub" class="text-xs text-gray-500"></p>
            </div>
            <form id="rate-form" method="POST" action="" class="space-y-4 px-4 py-4">
                @csrf
                <div>
                    <p class="mb-2 text-xs font-semibold text-gray-600">Rating</p>
                    <div class="flex gap-1" id="star-row" role="group" aria-label="Star rating">
                        @for ($s = 1; $s <= 5; $s++)
                            <button type="button" data-star="{{ $s }}"
                                class="star-btn rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-2 text-xl leading-none text-amber-400 transition hover:bg-amber-50">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="5">
                </div>
                <div>
                    <label for="comment-input" class="mb-1 block text-xs font-semibold text-gray-600">Comment
                        (optional)</label>
                    <textarea id="comment-input" name="comment" rows="3" maxlength="500"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-green-500"
                        placeholder="How was your meal or service?"></textarea>
                </div>
                <div class="flex gap-2 pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <button type="button" id="rate-cancel"
                        class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="flex-1 rounded-xl bg-green-600 py-3 text-sm font-semibold text-white hover:bg-green-700">Submit</button>
                </div>
            </form>
        </div>
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

                const modal = document.getElementById('rate-modal');
                const form = document.getElementById('rate-form');
                const ratingInput = document.getElementById('rating-input');
                const sub = document.getElementById('rate-modal-sub');
                const feedbackBase = @json(url('/student/orders'));

                document.querySelectorAll('.rate-order-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-order-id');
                        const label = btn.getAttribute('data-order-label') || '';
                        form.action = feedbackBase + '/' + encodeURIComponent(id) + '/feedback';
                        sub.textContent = label;
                        ratingInput.value = '5';
                        document.getElementById('comment-input').value = '';
                        syncStars(5);
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    });
                });

                function syncStars(n) {
                    document.querySelectorAll('.star-btn').forEach(b => {
                        const v = parseInt(b.getAttribute('data-star'), 10);
                        const on = v <= n;
                        b.classList.toggle('bg-amber-100', on);
                        b.classList.toggle('border-amber-300', on);
                        b.classList.toggle('bg-gray-50', !on);
                        b.classList.toggle('border-gray-200', !on);
                    });
                }

                document.querySelectorAll('.star-btn').forEach(b => {
                    b.addEventListener('click', () => {
                        const n = parseInt(b.getAttribute('data-star'), 10);
                        ratingInput.value = String(n);
                        syncStars(n);
                    });
                });
                syncStars(5);

                document.getElementById('rate-cancel')?.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                });
            });
        </script>
    @endpush
</x-layouts.student>
