<x-layouts.student title="My wallet" active="wallet">
    @if (session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
            @if (session('transfer_receipt_id'))
                <a href="{{ route('student.wallet.transfer.receipt', ['walletTransfer' => session('transfer_receipt_id')]) }}"
                    class="ml-2 font-semibold underline underline-offset-2">
                    View digital receipt
                </a>
            @endif
        </div>
    @endif

    @if ($errors->has('connection') || $errors->has('transfer') || $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('connection') ?: ($errors->first('transfer') ?: $errors->first()) }}
        </div>
    @endif

    <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg"
        style="background:linear-gradient(135deg,#16a34a 0%,#16a34a 50%,#169a45 100%);">
        <p class="mb-2 text-sm font-semibold" style="color:#dcfce7;">Total balance (all canteens)</p>
        <p class="mb-4 text-5xl font-bold tracking-tight" style="color:#ffffff;">₱{{ number_format($wallet['balance'], 2) }}</p>
        @if (!empty($wallet['college']))
            <div class="rounded-xl px-4 py-3" style="background-color:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.26);">
                <p class="mb-1 text-xs font-medium" style="color:#d1fae5;">College on profile</p>
                <p class="text-sm font-bold" style="color:#ffffff;">{{ $wallet['college'] }}</p>
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

    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/95 px-4 py-3 text-xs leading-relaxed text-emerald-950 shadow-sm">
        <p class="font-bold text-emerald-900">Load wallet via QR</p>
        <p>Generate a load QR, show it to the canteen staff, and they will scan then confirm your cash top-up.</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
        <h3 class="text-sm font-bold text-gray-800">Generate wallet-load QR</h3>
        <form method="post" action="{{ route('student.wallet.load-qr') }}" class="mt-3 grid gap-2 sm:grid-cols-4">
            @csrf
            <select name="college" class="rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                <option value="">Select canteen</option>
                @foreach (($topUpCanteens ?? []) as $c)
                    <option value="{{ $c['slug'] }}">{{ $c['label'] }}</option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="1" name="amount" required placeholder="Cash amount"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <input type="text" name="note" maxlength="500" placeholder="Optional note"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <button type="submit"
                class="rounded-lg px-3 py-2 text-sm font-bold shadow-sm"
                style="background-color:#92400e;color:#ffffff;border:1px solid #78350f;">Generate QR</button>
        </form>
    </div>

    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-green-50 via-emerald-50 to-green-100 p-4 shadow-sm">
        <h3 class="text-sm font-bold text-emerald-900">Connections</h3>
        <div class="mt-3 flex flex-wrap gap-2">
            <input type="text" id="connection-search-input" name="friend_student_id" placeholder="Search by name or student ID"
                class="flex-1 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm" required>
            <button type="button" id="connection-search-btn" aria-label="Search students"
                class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-bold shadow-sm"
                style="background-color:#047857;color:#ffffff;border:1px solid #065f46;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                </svg>
            </button>
        </div>

        <div id="connection-search-results" class="mt-3 space-y-2"></div>

        <div class="mt-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Incoming requests</p>
            @forelse (($incomingConnectionRequests ?? collect()) as $requestItem)
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $requestItem->requester?->name ?? 'Student' }}</p>
                        <p class="text-xs text-gray-600">{{ $requestItem->requester?->student_id ?? 'No ID' }} · {{ $requestItem->requester?->email ?? '' }}</p>
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
                <p class="mt-2 text-xs text-gray-500">No incoming requests.</p>
            @endforelse
        </div>

        <div class="mt-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Connected students</p>
            @forelse (($connectionDetails ?? []) as $conn)
                <details class="mt-2 rounded-xl border border-emerald-200 bg-white">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $conn['name'] }}</p>
                            <p class="text-xs text-gray-600">{{ $conn['student_id'] ?: 'No student ID' }} · {{ $conn['email'] }}</p>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700">View details</span>
                    </summary>
                    <div class="border-t border-emerald-100 px-3 py-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Wallet transfer history</p>
                            <form method="post" action="{{ route('student.wallet.connection.remove', $conn['id']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700">
                                    Remove connection
                                </button>
                            </form>
                        </div>
                        @if (empty($conn['history']))
                            <p class="mt-2 text-xs text-gray-500">No transfer history yet.</p>
                        @else
                            <div class="mt-2 space-y-1">
                                @foreach ($conn['history'] as $hist)
                                    <div class="flex items-center justify-between rounded-md bg-gray-50 px-2 py-1 text-xs">
                                        <span class="{{ $hist['type'] === 'sent' ? 'text-rose-600' : 'text-emerald-700' }}">
                                            {{ $hist['type'] === 'sent' ? 'Sent' : 'Received' }}
                                        </span>
                                        <span class="font-semibold text-gray-800">₱{{ number_format((float) $hist['amount'], 2) }}</span>
                                        <span class="text-gray-500">{{ $hist['date'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>
            @empty
                <p class="mt-2 text-xs text-gray-500">No active connections yet.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-green-50 to-emerald-100 p-4 shadow-sm">
        <h3 class="text-sm font-bold text-emerald-900">Transfer wallet balance</h3>
        <form action="{{ route('student.wallet.transfer') }}" method="post" class="mt-3 grid gap-2 sm:grid-cols-4">
            @csrf
            <select name="receiver_user_id" class="rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm" required>
                <option value="">Select friend</option>
                @foreach (($connections ?? []) as $conn)
                    <option value="{{ $conn->id }}">{{ $conn->name }}</option>
                @endforeach
            </select>
            @if (!empty($senderTransferCollege))
                <input type="hidden" name="college" value="{{ $senderTransferCollege['slug'] }}">
                <div class="rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm text-emerald-900">
                    Transfer canteen: <span class="font-bold">{{ $senderTransferCollege['label'] }}</span>
                </div>
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    Set your college on profile first to enable wallet transfer.
                </div>
            @endif
            <input type="number" step="1" min="1" name="amount" required placeholder="Amount (whole number)"
                class="rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm">
            <input type="text" name="note" maxlength="255" placeholder="Optional note"
                class="rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm sm:col-span-3">
            <button type="submit"
                class="rounded-lg px-3 py-2 text-sm font-bold shadow-sm"
                style="background-color:#065f46;color:#ffffff;border:1px solid #064e3b;"
                {{ empty($senderTransferCollege) ? 'disabled' : '' }}>Send</button>
        </form>
    </div>

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
            class="w-full rounded-xl py-3 text-sm font-bold shadow-sm transition"
            style="background-color:#065f46;color:#ffffff;border:1px solid #064e3b;">
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
                @if (!empty($transaction['receipt_url']))
                    <div class="mt-3">
                        <a href="{{ $transaction['receipt_url'] }}"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                            {{ $transaction['receipt_label'] ?? 'View digital receipt' }}
                        </a>
                    </div>
                @endif
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

    @push('scripts')
        <script>
            (function() {
                document.getElementById('btn-wallet-scroll-transactions')?.addEventListener('click', function() {
                    document.getElementById('wallet-transactions')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            })();
        </script>
        <script>
            const unreadBadge = document.getElementById('unread-badge');
            const unreadCountEndpoint = @json(route('student.unread-count'));
            const searchInput = document.getElementById('connection-search-input');
            const searchBtn = document.getElementById('connection-search-btn');
            const searchResults = document.getElementById('connection-search-results');
            const searchEndpoint = @json(route('student.wallet.connection-search'));
            const connectionRequestEndpoint = @json(route('student.wallet.connection-request'));
            const walletPageUrl = @json(route('student.wallet'));
            const csrfToken = @json(csrf_token());

            function escapeHtml(text) {
                return String(text ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function renderSearchResults(students, query) {
                if (!searchResults) return;

                if (!students.length) {
                    searchResults.innerHTML = `<p class="text-sm text-gray-600">No students found for "<strong>${escapeHtml(query)}</strong>".</p>`;
                    return;
                }

                const html = students.map((student) => {
                    const avatar = student.avatar_url
                        ? `<img src="${escapeHtml(student.avatar_url)}" alt="${escapeHtml(student.name)}" class="h-10 w-10 rounded-full object-cover ring-1 ring-emerald-200">`
                        : `<div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-800 ring-1 ring-emerald-200">${escapeHtml((student.name || '?').charAt(0).toUpperCase())}</div>`;
                    const actionButton = student.relation_status === 'connected'
                        ? `<span class="rounded-lg bg-emerald-100 px-3 py-2 text-xs font-bold text-emerald-800">Connected</span>`
                        : (student.relation_status === 'pending_sent'
                            ? `<span class="rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-800">Request sent</span>`
                            : (student.relation_status === 'pending_received'
                                ? `<a href="${escapeHtml(walletPageUrl)}" class="rounded-lg bg-emerald-100 px-3 py-2 text-xs font-bold text-emerald-800">Pending in incoming list</a>`
                                : `<form method="post" action="${escapeHtml(connectionRequestEndpoint)}">
                            <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                            <input type="hidden" name="friend_user_id" value="${student.id}">
                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold shadow-sm" style="background-color:#065f46;color:#ffffff;border:1px solid #064e3b;">Send request</button>
                        </form>`));

                    return `<div class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                        <div class="flex items-center gap-3">
                            ${avatar}
                            <div>
                                <p class="text-sm font-semibold text-gray-900">${escapeHtml(student.name)}</p>
                                <p class="text-xs text-gray-600">${escapeHtml(student.student_id || 'No student ID')}</p>
                                <p class="text-xs text-gray-500">${escapeHtml(student.email)}</p>
                                <p class="text-xs text-emerald-800">${escapeHtml(student.college || 'No college')}</p>
                            </div>
                        </div>
                        <div>${actionButton}</div>
                    </div>`;
                }).join('');

                searchResults.innerHTML = html;
            }

            async function searchStudents() {
                const query = (searchInput?.value || '').trim();
                if (!query) {
                    if (searchResults) searchResults.innerHTML = '<p class="text-sm text-gray-600">Type a student name to search.</p>';
                    return;
                }
                if (searchResults) searchResults.innerHTML = '<p class="text-sm text-gray-600">Searching students...</p>';
                try {
                    const response = await fetch(`${searchEndpoint}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });
                    if (!response.ok) throw new Error('Search failed');
                    const data = await response.json();
                    renderSearchResults(Array.isArray(data.students) ? data.students : [], query);
                } catch (e) {
                    if (searchResults) {
                        searchResults.innerHTML = '<p class="text-sm text-red-600">Unable to search right now. Please try again.</p>';
                    }
                }
            }

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
            searchResults.innerHTML = '<p class="text-sm text-gray-600">Type a student name to search.</p>';
            searchBtn?.addEventListener('click', searchStudents);
            searchInput?.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchStudents();
                }
            });
        </script>
    @endpush
</x-layouts.student>
