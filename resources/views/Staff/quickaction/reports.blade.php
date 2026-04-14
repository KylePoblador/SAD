<x-layouts.staff-subpage title="Reports & Analytics" :subtitle="$canteenName">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }
        }
    </style>

    <div class="no-print rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('staff.reports') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600" for="from">From</label>
                <input id="from" name="from" type="date" value="{{ $from }}"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600" for="to">To</label>
                <input id="to" name="to" type="date" value="{{ $to }}"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Apply</button>
            <a href="{{ route('staff.reports') }}"
                class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Reset</a>
            <a href="{{ route('staff.reports.print', ['from' => $from, 'to' => $to]) }}" target="_blank"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                Print report
            </a>
        </form>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Income (successful)</p>
            <p class="mt-2 text-2xl font-bold text-emerald-800">₱{{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Successful orders</p>
            <p class="mt-2 text-2xl font-bold text-blue-800">{{ $successfulOrdersCount }}</p>
        </div>
        <div class="rounded-xl border border-red-100 bg-red-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Cancelled orders</p>
            <p class="mt-2 text-2xl font-bold text-red-800">{{ $cancelledOrdersCount }}</p>
        </div>
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Cancelled amount</p>
            <p class="mt-2 text-2xl font-bold text-amber-800">₱{{ number_format($cancelledAmount, 2) }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="mb-3 text-sm font-bold text-gray-900">Top selling items (successful orders)</p>
        @forelse ($topItems as $item)
            <div class="flex items-center justify-between border-b border-gray-100 py-3 last:border-0">
                <div>
                    <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                    <p class="text-xs text-gray-500">{{ (int) $item->sold }} sold</p>
                </div>
                <span class="font-bold text-green-600">₱{{ number_format((float) $item->total, 2) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No successful sales in selected period.</p>
        @endforelse
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="mb-3 text-sm font-bold text-gray-900">Successful order list</p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="py-2 pr-3">Order #</th>
                        <th class="py-2 pr-3">Customer</th>
                        <th class="py-2 pr-3">Date</th>
                        <th class="py-2 pr-3">Items</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($successfulOrders as $order)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-2 pr-3 font-medium text-gray-900">{{ $order->order_number ?? 'ORD-'.$order->id }}</td>
                            <td class="py-2 pr-3">{{ $order->user->name ?? 'Student' }}</td>
                            <td class="py-2 pr-3 text-gray-500">{{ $order->created_at?->format('M d, Y H:i') }}</td>
                            <td class="py-2 pr-3">{{ (int) $order->items->sum('qty') }}</td>
                            <td class="py-2 text-right font-semibold text-emerald-700">₱{{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">No successful orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="mb-3 text-sm font-bold text-gray-900">Cancelled order list</p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="py-2 pr-3">Order #</th>
                        <th class="py-2 pr-3">Customer</th>
                        <th class="py-2 pr-3">Date</th>
                        <th class="py-2 pr-3">Items</th>
                        <th class="py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cancelledOrders as $order)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-2 pr-3 font-medium text-gray-900">{{ $order->order_number ?? 'ORD-'.$order->id }}</td>
                            <td class="py-2 pr-3">{{ $order->user->name ?? 'Student' }}</td>
                            <td class="py-2 pr-3 text-gray-500">{{ $order->created_at?->format('M d, Y H:i') }}</td>
                            <td class="py-2 pr-3">{{ (int) $order->items->sum('qty') }}</td>
                            <td class="py-2 text-right font-semibold text-red-700">₱{{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">No cancelled orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-gray-500">Total orders in period: {{ $totalOrders }}</p>
    </div>
</x-layouts.staff-subpage>
