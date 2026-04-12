<x-layouts.staff-subpage title="Reports & Analytics" subtitle="Today's summary">
    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Today's revenue</p>
        <p class="mt-2 text-3xl font-bold text-green-600">₱{{ number_format($todayRevenue ?? 0, 2) }}</p>
        <p class="mt-1 text-xs text-gray-500">From {{ $totalOrders ?? 0 }} orders</p>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="mb-3 text-sm font-bold text-gray-900">Top selling items</p>

        @if (isset($topItems) && count($topItems) > 0)
            @foreach ($topItems as $item)
                <div class="flex items-center justify-between border-b border-gray-100 py-3 last:border-0">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $item->name ?? 'Item' }}</p>
                        <p class="text-xs text-gray-500">{{ $item->sold ?? 0 }} sold</p>
                    </div>
                    <span class="font-bold text-green-600">₱{{ number_format($item->total ?? 0, 2) }}</span>
                </div>
            @endforeach
        @else
            <p class="text-sm text-gray-500">No sales today.</p>
        @endif

        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 font-bold text-gray-900">
            <span>Total</span>
            <span class="text-green-600">₱{{ number_format($totalSales ?? 0, 2) }}</span>
        </div>
    </div>
</x-layouts.staff-subpage>
