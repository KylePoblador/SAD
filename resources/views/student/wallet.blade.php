<x-layouts.student title="My wallet" active="wallet">
    @if (session('error') === 'wallet-load-qr-expired')
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 shadow-sm">
            <p class="font-semibold">That wallet QR expired.</p>
            <p class="mt-1 text-amber-900/90">Generate a new code from <strong>Load wallet</strong>.</p>
        </div>
    @endif
    @if (session('error') === 'wallet-load-qr-used')
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900 shadow-sm">
            <p class="font-semibold">This QR was already used.</p>
            <p class="mt-1 text-green-800/90">Staff confirmed your load. Generate a new QR if you need another top-up.</p>
        </div>
    @endif

    @if ($errors->any() && old('_form') === 'wallet-load-qr')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($pendingFriendRequests) && $pendingFriendRequests->isNotEmpty())
        @foreach($pendingFriendRequests as $req)
            <div class="mb-4 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <img src="{{ $req->user->avatarPublicUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($req->user->name) }}" class="h-10 w-10 rounded-full object-cover shadow-sm">
                    <div>
                        <p class="text-sm font-bold text-amber-900">{{ $req->user->name }}</p>
                        <p class="text-xs text-amber-800/80">sent you a friend request</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form method="post" action="{{ route('student.friends.accept', $req) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-green-700 shadow-sm">Accept</button>
                    </form>
                    <form method="post" action="{{ route('student.friends.reject', $req) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full p-2 text-gray-400 hover:bg-amber-100 hover:text-gray-600 transition" aria-label="Decline Request">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
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
            <strong>that</strong> place. Here you see every canteen and how they add up to your total above.
        </p>
        @forelse ($wallet['canteen_balances'] ?? [] as $row)
            <div
                class="mb-2 flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/80 px-3 py-2.5 last:mb-0">
                <span class="text-sm font-medium text-gray-800">{{ $row['label'] }}</span>
                <span class="text-sm font-bold text-green-700">₱{{ number_format($row['balance'], 2) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No canteens listed yet. When staff open a canteen, it will appear here
                (₱0.00
                until you top up there).</p>
        @endforelse
    </div>

    <div
        class="mt-4 space-y-2 rounded-2xl border border-emerald-200 bg-emerald-50/95 px-4 py-3 text-xs leading-relaxed text-emerald-950 shadow-sm">
        <p class="font-bold text-emerald-900">Cash top-up</p>
        <p>Open <strong>Load wallet</strong>, enter your amount and canteen, then show the QR after you pay cash.
            Staff scan it once — your balance updates right away.</p>
    </div>

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
            <span class="mt-1.5 max-w-[260px] text-center text-xs font-medium leading-snug text-amber-800/75">Generate a QR for staff after you choose amount and canteen.</span>
        </span>
    </button>

    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-bold text-indigo-900">Connect · Share coins</h3>
                <p class="mt-1 text-xs text-indigo-900/80">Transfer CEIT balance to your accepted friends.</p>
            </div>
            <a href="{{ route('student.friends.index') }}" class="rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-200">
                Manage Friends
            </a>
        </div>
        @if ($errors->has('connect'))
            <p class="mt-2 text-xs font-semibold text-red-600">{{ $errors->first('connect') }}</p>
        @endif
        @if(($connectRecipients ?? collect())->isEmpty())
            <div class="rounded-xl border border-indigo-100 bg-white/60 p-6 text-center">
                <p class="text-sm font-medium text-indigo-900">You don't have any friends yet.</p>
                <p class="mt-1 mb-3 text-xs text-indigo-700/80">Add friends first before you can send CEIT coins.</p>
                <a href="{{ route('student.friends.index') }}" class="inline-block rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Go to Manage Friends
                </a>
            </div>
        @else
            <form method="post" action="{{ route('student.connect.send') }}" class="space-y-2">
                @csrf
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input id="connect-search" type="text" placeholder="Search your friends by name / email..."
                        class="w-full rounded-xl border border-indigo-200 pl-9 pr-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
                
                <select id="connect-user" name="receiver_user_id" required
                    class="w-full rounded-xl border border-indigo-200 px-3 py-2 text-sm">
                    <option value="">Select friend</option>
                    @foreach ($connectRecipients as $u)
                        <option value="{{ $u->id }}">
                            {{ $u->name }} ({{ $u->student_id ?: $u->email }})
                        </option>
                    @endforeach
                </select>
                <select name="college" required class="w-full rounded-xl border border-indigo-200 px-3 py-2 text-sm">
                    <option value="">Select canteen balance</option>
                    @foreach ($canSendCoinsFrom ?? [] as $row)
                        <option value="{{ $row['slug'] }}">{{ $row['label'] }}
                            (₱{{ number_format($row['balance'], 2) }})</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount"
                    class="w-full rounded-xl border border-indigo-200 px-3 py-2 text-sm" required>
                <input type="text" name="note" maxlength="300" placeholder="Note (optional)"
                    class="w-full rounded-xl border border-indigo-200 px-3 py-2 text-sm">
                <button type="submit"
                    class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    Send coins
                </button>
            </form>
        @endif
    </div>

    <a href="{{ route('student.refunds') }}"
        class="group flex flex-col items-center gap-3 rounded-2xl border-2 border-green-200/90 bg-white p-1 shadow-sm ring-1 ring-green-100/80 transition hover:border-green-300 hover:shadow-md active:scale-[0.99]">
        <span class="flex flex-col items-center rounded-xl px-4 py-6 w-full">
            <span
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 shadow-md shadow-green-300/50 transition group-hover:shadow-lg group-hover:shadow-green-300/60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="text-lg font-bold tracking-tight text-green-900">My Refunds</span>
            <span class="mt-1.5 max-w-[260px] text-center text-xs font-medium leading-snug text-green-800/75">View all
                refunds you've received from staff.</span>
        </span>
    </a>

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
                        @if (!empty($transaction['receipt_url']))
                            <a href="{{ $transaction['receipt_url'] }}"
                                class="mt-1 inline-block text-xs font-semibold text-indigo-700 underline">Open
                                printable receipt</a>
                        @endif
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
                Choose the canteen where you’re paying cash and the exact amount. We’ll show a QR — staff scan it at the counter (same QR scanner they use for orders) to credit your wallet.
            </p>
            @if (count($topUpCanteens ?? []) > 0)
                <form method="post" action="{{ route('student.wallet.load-qr.generate') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="_form" value="wallet-load-qr">
                    <div>
                        <label for="deposit-college"
                            class="mb-1 block text-xs font-semibold text-gray-700">Canteen</label>
                        <select id="deposit-college" name="college" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                            <option value="" disabled
                                {{ old('_form') === 'wallet-load-qr' && old('college') ? '' : 'selected' }}>
                                Select a canteen</option>
                            @foreach ($topUpCanteens as $c)
                                <option value="{{ $c['slug'] }}"
                                    {{ old('_form') === 'wallet-load-qr' && old('college') === $c['slug'] ? 'selected' : '' }}>
                                    {{ $c['label'] }}@if (!empty($c['dist']))
                                        — {{ $c['dist'] }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="deposit-amount" class="mb-1 block text-xs font-semibold text-gray-700">Amount you will pay (cash)</label>
                        <input type="number" step="0.01" min="0.01" name="amount"
                            id="deposit-amount"
                            value="{{ old('_form') === 'wallet-load-qr' ? old('amount') : '' }}"
                            placeholder="e.g. 200"
                            required
                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                    <button type="submit"
                        class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                        Show QR for staff
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
                @if ($errors->any() && old('_form') === 'wallet-load-qr')
                    openTopupModal();
                @endif
                document.getElementById('btn-wallet-scroll-transactions')?.addEventListener('click', function() {
                    document.getElementById('wallet-transactions')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            })();
        </script>
        <script>
            (function() {
                const searchInput = document.getElementById('connect-search');
                const select = document.getElementById('connect-user');
                const endpoint = @json(route('student.connect.search'));
                if (!searchInput || !select || !endpoint) return;
                let timer = null;
                searchInput.addEventListener('input', function() {
                    clearTimeout(timer);
                    const q = searchInput.value.trim();
                    timer = setTimeout(async function() {
                        if (q.length < 2) return;
                        try {
                            const res = await fetch(endpoint + '?q=' + encodeURIComponent(q), {
                                headers: {
                                    Accept: 'application/json'
                                }
                            });
                            const data = await res.json();
                            select.innerHTML = '<option value="">Select friend</option>';
                            (data.items || []).forEach(function(u) {
                                const opt = document.createElement('option');
                                opt.value = String(u.id);
                                opt.textContent = u.name + ' (' + u.email + ')';
                                select.appendChild(opt);
                            });
                            
                            if (data.items.length === 0) {
                                select.innerHTML = '<option value="">No friends found</option>';
                            }
                        } catch (e) {}
                    }, 300);
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
