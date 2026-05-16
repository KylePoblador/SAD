<x-layouts.student title="My Transactions" active="wallet">

    <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold text-gray-900">All Transactions</h1>
        <a href="{{ route('student.wallet') }}" class="text-sm font-semibold text-green-600 hover:text-green-700">← Wallet</a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('student.transactions') }}" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm space-y-3">
        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Filter transactions</p>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-600">Type</label>
            <select name="type" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="all"          {{ $type === 'all'           ? 'selected' : '' }}>All types</option>
                <option value="order"        {{ $type === 'order'         ? 'selected' : '' }}>Canteen Orders</option>
                <option value="wallet_load"  {{ $type === 'wallet_load'   ? 'selected' : '' }}>Wallet Loads</option>
                <option value="coin_transfer"{{ $type === 'coin_transfer' ? 'selected' : '' }}>Coin Transfers</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 rounded-xl bg-green-600 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                Apply filter
            </button>
            <a href="{{ route('student.transactions') }}"
                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    {{-- Results count --}}
    <p class="text-xs text-gray-500">
        Showing <strong>{{ $transactions->count() }}</strong> transaction{{ $transactions->count() !== 1 ? 's' : '' }}
    </p>

    {{-- Transaction list --}}
    @forelse ($transactions as $tx)
        @php
            $badgeClass = match($tx['badge']) {
                'green'  => 'bg-emerald-100 text-emerald-700',
                'purple' => 'bg-violet-100 text-violet-700',
                default  => 'bg-blue-100 text-blue-700',
            };
        @endphp
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 mb-1">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $badgeClass }}">
                            {{ $tx['type_label'] }}
                        </span>
                        @if($tx['flow'] === 'credit')
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-green-700">Credit</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-700">Debit</span>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $tx['description'] }}</p>
                    @if(!empty($tx['sub']))
                        <p class="text-xs text-gray-500">{{ $tx['sub'] }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">{{ $tx['date']->format('M d, Y · g:i A') }}</p>
                    @if(!empty($tx['receipt_url']))
                        <a href="{{ $tx['receipt_url'] }}"
                            class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            View &amp; download receipt
                        </a>
                    @endif
                </div>
                <p class="shrink-0 text-lg font-bold {{ $tx['flow'] === 'debit' ? 'text-red-500' : 'text-green-600' }}">
                    {{ $tx['flow'] === 'debit' ? '-' : '+' }}₱{{ number_format($tx['amount'], 2) }}
                </p>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-10 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-sm font-medium text-gray-500">No transactions found</p>
            <p class="mt-1 text-xs text-gray-400">Try changing the filter above.</p>
        </div>
    @endforelse

</x-layouts.student>
