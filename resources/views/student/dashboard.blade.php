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

    <div class="relative overflow-hidden rounded-xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-green-100 to-emerald-200/80 p-4 shadow-sm">
        <div class="pointer-events-none absolute -left-8 -top-8 h-24 w-24 rounded-full bg-emerald-600/20 blur-xl"></div>
        <div class="relative z-10 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Wallet Connections</p>
                <p class="mt-1 text-sm text-emerald-950">
                    {{ (int) ($connectionCount ?? 0) }} {{ (int) ($connectionCount ?? 0) === 1 ? 'connection' : 'connections' }}
                    ready for transfer
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-toggle-connections"
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-bold shadow-lg ring-1 ring-emerald-950/20 transition focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    style="background-color:#047857;color:#ffffff;border:1px solid #065f46;">
                    View connections
                </button>
                <a href="{{ route('student.wallet') }}"
                    class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-bold shadow-lg ring-1 ring-emerald-950/20 transition focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    style="background-color:#065f46;color:#ffffff;border:1px solid #064e3b;">
                    Open wallet
                </a>
            </div>
        </div>
        <div id="dashboard-connections-panel" class="relative z-10 mt-3 hidden rounded-xl bg-white/85 p-3 ring-1 ring-emerald-100">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-emerald-800">Your connected students</p>
            @if (($connections ?? collect())->isEmpty())
                <p class="text-sm text-emerald-900">No connections yet. Add students first in Wallet.</p>
            @else
                <input type="text" id="dashboard-connections-search" placeholder="Search by name"
                    class="mb-3 w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <div id="dashboard-connections-list" class="grid gap-2 sm:grid-cols-2">
                    @foreach ($connections as $conn)
                        <div class="dashboard-connection-item rounded-lg border border-emerald-200 bg-white px-3 py-2"
                            data-name="{{ strtolower((string) ($conn->name ?? '')) }}">
                            <p class="text-sm font-semibold text-gray-800">{{ $conn->name }}</p>
                            @if (!empty($conn->student_id))
                                <p class="text-xs text-gray-500">{{ $conn->student_id }}</p>
                            @endif
                            @if (!empty($conn->email))
                                <p class="text-xs text-gray-500">{{ $conn->email }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p id="dashboard-connections-empty-search" class="mt-2 hidden text-sm text-gray-600">No matching student name.</p>
            @endif

            <div class="mt-4 border-t border-emerald-100 pt-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-emerald-800">Incoming requests</p>
                @forelse (($incomingConnectionRequests ?? collect()) as $requestItem)
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 last:mb-0">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $requestItem->requester?->name ?? 'Student' }}</p>
                            <p class="text-xs text-gray-500">{{ $requestItem->requester?->student_id ?? 'No ID' }} · {{ $requestItem->requester?->email ?? '' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="post" action="{{ route('student.wallet.connection-request.respond', $requestItem) }}">
                                @csrf
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-bold"
                                    style="background-color:#065f46;color:#ffffff;border:1px solid #064e3b;">Accept</button>
                            </form>
                            <form method="post" action="{{ route('student.wallet.connection-request.respond', $requestItem) }}">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-bold text-rose-700">Reject</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No incoming requests yet.</p>
                @endforelse
            </div>

            <div class="mt-4 border-t border-emerald-100 pt-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-emerald-800">Sent requests</p>
                @forelse (($outgoingConnectionRequests ?? collect()) as $requestItem)
                    <div class="mb-2 flex items-center justify-between gap-2 rounded-lg border border-rose-200 bg-rose-50/80 px-3 py-2 last:mb-0">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $requestItem->receiver?->name ?? 'Student' }}</p>
                            <p class="text-xs text-gray-500">{{ $requestItem->receiver?->student_id ?? 'No ID' }} · {{ $requestItem->receiver?->email ?? '' }}</p>
                        </div>
                        <form method="post" action="{{ route('student.wallet.connection-request.cancel', $requestItem) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="rounded-lg px-3 py-2 text-xs font-bold shadow-sm transition"
                                style="background:#dc2626 !important;color:#ffffff !important;border:1px solid #b91c1c !important;opacity:1 !important;">
                                Cancel request
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No pending sent requests.</p>
                @endforelse
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
            const toggleConnectionsBtn = document.getElementById('btn-toggle-connections');
            const connectionsPanel = document.getElementById('dashboard-connections-panel');
            const connectionsSearchInput = document.getElementById('dashboard-connections-search');
            const connectionItems = Array.from(document.querySelectorAll('.dashboard-connection-item'));
            const noSearchResultText = document.getElementById('dashboard-connections-empty-search');

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

            toggleConnectionsBtn?.addEventListener('click', function() {
                if (!connectionsPanel) return;
                const isHidden = connectionsPanel.classList.contains('hidden');
                connectionsPanel.classList.toggle('hidden');
                toggleConnectionsBtn.textContent = isHidden ? 'Hide connections' : 'View connections';
            });

            connectionsSearchInput?.addEventListener('input', function() {
                const query = (connectionsSearchInput.value || '').toLowerCase().trim();
                let visibleCount = 0;

                connectionItems.forEach(function(item) {
                    const name = item.dataset.name || '';
                    const show = query === '' || name.includes(query);
                    item.classList.toggle('hidden', !show);
                    if (show) visibleCount += 1;
                });

                if (noSearchResultText) {
                    noSearchResultText.classList.toggle('hidden', visibleCount > 0);
                }
            });
        </script>
    @endpush
</x-layouts.student>
