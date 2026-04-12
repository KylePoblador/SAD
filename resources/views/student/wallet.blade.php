<x-layouts.student title="My wallet" active="wallet">
    @if (session('status') === 'deposit-inquiry-sent')
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900 shadow-sm">
            <p class="font-semibold">Your canteen has been notified.</p>
            <p class="mt-1 text-green-800/90">Go to <strong>{{ session('deposit_target') }}</strong>, pay cash at the counter —
                staff will load your wallet when you arrive.</p>
            @if (session('deposit_college_slug'))
                <a href="{{ route('student.canteen', session('deposit_college_slug')) }}"
                    class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-green-600 py-2.5 text-sm font-semibold text-white hover:bg-green-700 sm:w-auto sm:px-5">
                    Open this canteen
                </a>
            @endif
        </div>
    @endif

    @if ($errors->any() && old('_form') === 'wallet-deposit-inquiry')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="relative overflow-hidden rounded-2xl bg-green-600 p-6 text-white shadow-sm">
        <p class="mb-2 text-sm font-medium opacity-90">Total balance (all canteens)</p>
        <p class="mb-4 text-5xl font-bold">₱{{ number_format($wallet['balance'], 2) }}</p>
        @if (!empty($wallet['college']))
            <div class="rounded-xl bg-white/20 px-4 py-3 backdrop-blur-sm">
                <p class="mb-1 text-xs opacity-90">College on profile</p>
                <p class="text-sm font-semibold">{{ $wallet['college'] }}</p>
            </div>
        @endif
    </div>

    <div class="mt-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <h3 class="mb-3 text-sm font-bold text-gray-800">Balance per canteen</h3>
        <p class="mb-3 text-xs leading-relaxed text-gray-600">When you browse a canteen, you only see money for
            <strong>that</strong> place. Here you see every canteen and how they add up to your total above.</p>
        @forelse ($wallet['canteen_balances'] ?? [] as $row)
            <div class="mb-2 flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/80 px-3 py-2.5 last:mb-0">
                <span class="text-sm font-medium text-gray-800">{{ $row['label'] }}</span>
                <span class="text-sm font-bold text-green-700">₱{{ number_format($row['balance'], 2) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No canteens listed yet. When staff open a canteen, it will appear here (₱0.00
                until you top up there).</p>
        @endforelse
    </div>

    <div class="mt-4 space-y-2 rounded-2xl border border-emerald-200 bg-emerald-50/95 px-4 py-3 text-xs leading-relaxed text-emerald-950 shadow-sm">
        <p class="font-bold text-emerald-900">Top-up request</p>
        <p>A <strong>Notify canteen</strong> message does not move money yet. Your balance for that canteen goes up only
            after staff <strong>confirm load</strong> when you pay cash at their counter.</p>
    </div>

    {{-- Load wallet — same action as top-up inquiry modal (prominent card, mockup-style) --}}
    <button type="button" id="btn-wallet-topup-info"
        class="group w-full rounded-2xl border-2 border-amber-200/90 bg-white p-1 shadow-sm ring-1 ring-amber-100/80 transition hover:border-amber-300 hover:shadow-md active:scale-[0.99]">
        <span class="flex flex-col items-center rounded-xl px-4 py-6">
            <span
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-md shadow-orange-300/50 transition group-hover:shadow-lg group-hover:shadow-orange-300/60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </span>
            <span class="text-lg font-bold tracking-tight text-amber-900">Load wallet</span>
            <span class="mt-1.5 max-w-[260px] text-center text-xs font-medium leading-snug text-amber-800/75">Pick a canteen,
                notify staff, then pay at the counter.</span>
        </span>
    </button>

    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="mb-2 text-xs text-gray-500">Total spent</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($wallet['total_spent'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="mb-2 text-xs text-gray-500">Total orders</p>
            <p class="text-2xl font-bold text-gray-800">{{ $wallet['total_orders'] }}</p>
        </div>
    </div>

    <div>
        <button type="button" id="btn-wallet-scroll-transactions"
            class="w-full rounded-xl border border-gray-200 bg-white py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            View transactions
        </button>
    </div>

    <div id="wallet-transactions">
        <h2 class="mb-3 text-base font-bold text-gray-800">Recent transactions</h2>
        @forelse ($wallet['recent_transactions'] as $transaction)
            <div class="mb-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $transaction['description'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $transaction['date'] }}</p>
                    </div>
                    <p
                        class="text-lg font-bold {{ $transaction['type'] === 'debit' ? 'text-red-500' : 'text-green-600' }}">
                        {{ $transaction['type'] === 'debit' ? '-' : '+' }}₱{{ number_format($transaction['amount'], 2) }}
                    </p>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-500">No transactions yet</p>
                <p class="mt-1 text-xs text-gray-400">Your transactions will appear here</p>
            </div>
        @endforelse
    </div>

    <div id="wallet-topup-modal" role="dialog" aria-modal="true" aria-labelledby="wallet-topup-title"
        class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4 opacity-0 pointer-events-none transition-opacity duration-200">
        <div id="wallet-topup-backdrop" class="absolute inset-0 bg-black/45 backdrop-blur-[1px]"></div>
        <div id="wallet-topup-panel"
            class="relative z-10 w-full max-w-md rounded-t-2xl bg-white p-5 shadow-2xl transition-transform duration-300 ease-out sm:rounded-2xl translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">
            <h2 id="wallet-topup-title" class="text-lg font-bold text-gray-900">Load wallet</h2>
            <p class="mt-2 text-sm leading-relaxed text-gray-600">
                Choose where you will deposit. Staff will see your request on their dashboard. When you arrive, pay cash
                and they will use <strong>Load wallet</strong> to credit you.
            </p>
            @if (count($topUpCanteens ?? []) > 0)
                <form method="post" action="{{ route('student.wallet.deposit-inquiry') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="_form" value="wallet-deposit-inquiry">
                    <div>
                        <label for="deposit-college" class="mb-1 block text-xs font-semibold text-gray-700">Canteen</label>
                        <select id="deposit-college" name="college" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                            <option value="" disabled {{ old('_form') === 'wallet-deposit-inquiry' && old('college') ? '' : 'selected' }}>
                                Select a canteen</option>
                            @foreach ($topUpCanteens as $c)
                                <option value="{{ $c['slug'] }}"
                                    {{ old('_form') === 'wallet-deposit-inquiry' && old('college') === $c['slug'] ? 'selected' : '' }}>
                                    {{ $c['label'] }}@if (!empty($c['dist'])) — {{ $c['dist'] }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="deposit-amount" class="mb-1 block text-xs font-semibold text-gray-700">Amount
                            (optional)</label>
                        <input type="number" step="0.01" min="1" name="intended_amount" id="deposit-amount"
                            value="{{ old('_form') === 'wallet-deposit-inquiry' ? old('intended_amount') : '' }}"
                            placeholder="e.g. 200"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="deposit-note" class="mb-1 block text-xs font-semibold text-gray-700">Note
                            (optional)</label>
                        <input type="text" name="note" id="deposit-note" maxlength="500"
                            value="{{ old('_form') === 'wallet-deposit-inquiry' ? old('note') : '' }}"
                            placeholder="e.g. arriving around noon"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                    <button type="submit"
                        class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                        Notify canteen
                    </button>
                </form>
            @else
                <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    No canteen with a staff account is available right now. Try again later or contact an administrator.
                </p>
            @endif
            <button type="button" id="btn-wallet-topup-close"
                class="mt-4 w-full rounded-xl border border-gray-200 bg-white py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                var modal = document.getElementById('wallet-topup-modal');
                var panel = document.getElementById('wallet-topup-panel');
                var backdrop = document.getElementById('wallet-topup-backdrop');
                function openTopupModal() {
                    if (!modal || !panel) return;
                    modal.classList.remove('opacity-0', 'pointer-events-none');
                    modal.classList.add('opacity-100', 'pointer-events-auto');
                    panel.classList.remove('translate-y-full', 'sm:scale-95', 'sm:opacity-0');
                    panel.classList.add('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
                }
                function closeTopupModal() {
                    if (!modal || !panel) return;
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    modal.classList.remove('opacity-100', 'pointer-events-auto');
                    panel.classList.add('translate-y-full', 'sm:scale-95', 'sm:opacity-0');
                    panel.classList.remove('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
                }
                document.getElementById('btn-wallet-topup-info')?.addEventListener('click', openTopupModal);
                document.getElementById('btn-wallet-topup-close')?.addEventListener('click', closeTopupModal);
                backdrop?.addEventListener('click', closeTopupModal);
                @if ($errors->any() && old('_form') === 'wallet-deposit-inquiry')
                    openTopupModal();
                @endif
                document.getElementById('btn-wallet-scroll-transactions')?.addEventListener('click', function() {
                    document.getElementById('wallet-transactions')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            })();
        </script>
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
        </script>
    @endpush
</x-layouts.student>
