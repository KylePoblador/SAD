<x-layouts.student title="Dashboard" active="home">
    @php
        $fullName = trim(auth()->user()->name ?? '');
        $nameParts = $fullName !== '' ? preg_split('/\s+/', $fullName, 2) : [];
        $firstName = $nameParts[0] ?? 'there';
        $hour = (int) now()->format('G');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    @endphp

    {{-- Welcome (replaces wallet card — balance shows on each canteen page) --}}
    <div
        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 via-green-600 to-teal-800 p-6 text-white shadow-lg ring-1 ring-white/10 sm:p-8">
        <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-12 left-1/3 h-32 w-32 rounded-full bg-lime-300/20 blur-2xl"></div>
        <div class="relative">
            <p class="text-sm font-medium text-emerald-100/90">{{ $greeting }},</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">{{ $firstName }}</h1>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-emerald-50/95">
                Each canteen has its <strong class="text-white">own</strong> balance when you order there. Tap one below to
                see only that canteen’s funds — open <strong class="text-white">Wallet</strong> anytime for your
                <strong class="text-white">total</strong> everywhere.
            </p>
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
                    <span class="text-base leading-none">🍽️</span> Browse &amp; order
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
                    <span class="text-base leading-none">💺</span> Reserve a seat
                </span>
            </div>
        </div>
    </div>

<<<<<<< HEAD
    <div class="max-w-lg mx-auto px-4 py-4 space-y-4">
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
=======
    {{-- STATS --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4">
        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
>>>>>>> 980607e9b8e5596e4a05a6d50c45bece1dcc194e
            <div>
                <p class="text-xs text-gray-500">Active orders</p>
                <p class="text-xl font-bold text-gray-800">{{ $activeOrdersCount }}</p>
            </div>
        </div>
<<<<<<< HEAD

        {{-- Order Status + Feedback --}}
        <div id="order-status" class="bg-white rounded-xl p-4 shadow-sm">
            <h2 class="text-base font-bold text-gray-800">Order Status</h2>
            <p class="text-xs text-gray-500 mt-1">Only completed orders can provide feedback.</p>

            <div class="mt-3 space-y-3">
                @foreach ($orders as $order)
                    @php
                        $statusClasses = match ($order['status']) {
                            'completed' => 'bg-green-100 text-green-700',
                            'preparing' => 'bg-yellow-100 text-yellow-700',
                            'ready' => 'bg-indigo-100 text-indigo-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="rounded-lg border border-gray-100 px-3 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">#{{ $order['id'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Canteen: {{ $order['canteen'] ?? 'N/A' }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $statusClasses }}">{{ ucfirst($order['status']) }}</span>
                        </div>

                        @if ($order['status'] === 'completed')
                            @if (empty($feedbacks[$order['id']]))
                                <form id="provide-feedback" method="POST" action="{{ route('student.feedback.submit', $order['id']) }}"
                                    class="mt-2">
                                    @csrf
                                    <label for="feedback_{{ $order['id'] }}"
                                        class="block text-xs font-semibold text-gray-700 mb-1">Provide Feedback</label>
                                    <textarea id="feedback_{{ $order['id'] }}" name="feedback" rows="2"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                        placeholder="Write your feedback for this completed order..."></textarea>
                                    @error('feedback')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <button type="submit"
                                        class="mt-2 rounded-lg bg-green-500 px-3 py-2 text-xs font-semibold text-white hover:bg-green-600">
                                        Submit Feedback
                                    </button>
                                </form>
                            @else
                                <div class="mt-2 rounded-lg border border-green-100 bg-green-50 px-3 py-2">
                                    <p class="text-xs font-semibold text-green-700">Feedback Submitted</p>
                                    <p class="mt-1 text-sm text-green-900">"{{ $feedbacks[$order['id']] }}"</p>
                                </div>
                            @endif

                            @if (!empty($staffReplies[$order['id']]))
                                <div class="mt-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                                    <p class="text-xs font-semibold text-blue-700">Staff Reply</p>
                                    <p class="mt-1 text-sm text-blue-900">"{{ $staffReplies[$order['id']]['message'] }}"</p>
                                    @if (!empty($staffReplies[$order['id']]['replied_at']))
                                        <p class="mt-1 text-[11px] text-blue-700">{{ $staffReplies[$order['id']]['replied_at'] }}</p>
                                    @endif
                                </div>
                            @endif
                        @else
                            <p class="text-xs text-gray-500 mt-2">Feedback is disabled until this order is completed.</p>
                        @endif
                    </div>
                @endforeach
            </div>
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
=======
        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs text-gray-500">Total orders</p>
                <p class="text-xl font-bold text-gray-800">{{ $totalOrdersCount }}</p>
>>>>>>> 980607e9b8e5596e4a05a6d50c45bece1dcc194e
            </div>
        </div>
    </div>

    @if ($activeOrder)
        @php
            $statusLabel = match ($activeOrder->status) {
                'ready' => 'Ready for pickup',
                'preparing' => 'Preparing',
                default => 'In progress',
            };
        @endphp
        <div class="flex items-center justify-between rounded-xl border border-orange-200 bg-orange-50 px-4 py-3">
            <div>
                <p class="text-xs font-semibold text-orange-600">Active order</p>
                <p class="text-sm font-medium text-gray-800">{{ $activeOrder->order_number }} · {{ $statusLabel }}</p>
            </div>
        </div>
    @endif

    {{-- CANTEENS --}}
    <div>
        <h2 class="mb-3 text-base font-bold text-gray-800 md:text-lg">Browse canteens</h2>
        @if (count($canteens) === 0)
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500 shadow-sm">
                No canteens are open yet. When staff register for a college canteen, it will appear here.
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-4 xl:grid-cols-3">
                @foreach ($canteens as $canteen)
                    <a href="{{ route('student.canteen', $canteen['college']) }}"
                        class="flex items-center justify-between rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-sm transition hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $canteen['name'] }}</p>
                            <p class="text-xs text-gray-400">{{ strtoupper($canteen['college']) }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-gray-400">{{ $canteen['dist'] }}</span>
                                <span
                                    class="text-xs font-medium {{ $canteen['full'] ? 'text-red-500' : 'text-green-600' }}">
                                    ● {{ $canteen['seats'] }} seats
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Staff: {{ $canteen['staff_names'] }}</p>
                        </div>
                        <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-bold text-amber-800">
                            ★ {{ $canteen['rating'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
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
            setInterval(updateUnreadCount, 60000);
        </script>
    @endpush
</x-layouts.student>
