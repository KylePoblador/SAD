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

    {{-- STATS --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4">
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
