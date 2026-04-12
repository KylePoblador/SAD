@php
    /** @var \App\Models\Order $order */
    $rank = ['pending' => 0, 'preparing' => 1, 'ready' => 2, 'completed' => 3];
    $currentRank = $rank[$order->status] ?? 0;
@endphp

<x-layouts.staff-subpage title="Order detail" :subtitle="$canteenName ?? 'Canteen'">
    @if (session('status') === 'order-updated')
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            Order status updated.
        </div>
    @endif

    @if ($errors->has('status'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first('status') }}
        </div>
    @endif

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Order</p>
                <p class="text-lg font-bold text-gray-900">{{ $order->order_number ?? 'ORD-' . $order->id }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ $order->created_at?->format('l, F j, Y \a\t g:i A') }}</p>
            </div>
            @php
                $badge = match ($order->status) {
                    'pending' => 'bg-yellow-100 text-yellow-900',
                    'preparing' => 'bg-orange-100 text-orange-900',
                    'ready' => 'bg-green-100 text-green-900',
                    'completed' => 'bg-green-600 text-white',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp
            <span class="rounded-full px-3 py-1 text-xs font-bold capitalize {{ $badge }}">{{ $order->status }}</span>
        </div>

        <hr class="my-4 border-gray-100">

        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Customer</p>
        <p class="font-semibold text-gray-900">{{ $order->user->name ?? 'Unknown' }}</p>
        <p class="text-sm text-gray-500">ID {{ $order->user->id ?? '—' }}</p>

        <hr class="my-4 border-gray-100">

        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Items</p>
        <div class="space-y-2 text-sm">
            @forelse ($order->items ?? [] as $item)
                <div class="flex justify-between gap-2">
                    <span>{{ $item->name }} × {{ $item->qty }}</span>
                    <span class="font-medium text-gray-800">₱{{ number_format((float) $item->price * (int) $item->qty, 2) }}</span>
                </div>
            @empty
                <p class="text-gray-400">No items.</p>
            @endforelse
        </div>

        <div class="mt-4 flex justify-between border-t border-gray-100 pt-3 text-lg font-bold text-gray-900">
            <span>Total</span>
            <span class="text-green-600">₱{{ number_format((float) ($order->total ?? 0), 2) }}</span>
        </div>
    </div>

    @if ($order->status !== 'completed')
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-900">Update status</h2>
            <p class="mt-1 text-xs text-gray-500">You can move the order forward in the workflow (not backward).</p>

            <form method="post" action="{{ route('staff.orders.status', $order) }}" class="mt-4 space-y-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="from" value="detail">

                <div>
                    <label for="order-status" class="mb-1 block text-xs font-semibold text-gray-600">Status</label>
                    <select id="order-status" name="status"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
                        @foreach (['pending' => 'Pending', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Completed'] as $value => $label)
                            @php $r = $rank[$value] ?? 0; @endphp
                            <option value="{{ $value }}" @selected($order->status === $value) @disabled($r < $currentRank)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="w-full min-h-[44px] rounded-xl bg-green-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                    Save status
                </button>
            </form>
        </div>
    @endif

    <a href="{{ route('staff.orders', ['status' => $order->status]) }}"
        class="inline-flex text-sm font-semibold text-green-600 hover:text-green-700">← Back to order list</a>
</x-layouts.staff-subpage>
