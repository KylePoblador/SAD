@php
    $status = $status ?? 'pending';
    $statusCounts = $statusCounts ?? ['pending' => 0, 'preparing' => 0, 'ready' => 0, 'completed' => 0];
@endphp

<x-layouts.staff-subpage title="Order management" :subtitle="$canteenName ?? 'Canteen'">
    <x-slot:tabs>
        <div class="flex flex-wrap gap-2">
            @foreach (['pending' => 'Pending', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Completed'] as $key => $label)
                @php
                    $activeTab = match ($key) {
                        'pending' => 'bg-yellow-400 text-yellow-950 shadow-sm ring-1 ring-yellow-500/30',
                        'preparing' => 'bg-orange-500 text-white shadow-sm ring-1 ring-orange-600/30',
                        'ready' => 'bg-green-200 text-green-900 shadow-sm ring-1 ring-green-400/50',
                        'completed' => 'bg-green-600 text-white shadow-sm ring-1 ring-green-700/40',
                        default => 'bg-green-600 text-white',
                    };
                    $cnt = (int) ($statusCounts[$key] ?? 0);
                @endphp
                <a href="{{ route('staff.orders', ['status' => $key]) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $status === $key ? $activeTab : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                    {{ $label }}
                    @if ($cnt > 0)
                        <span class="ml-1 rounded-full bg-black/10 px-1.5 py-0.5 text-[10px]">{{ $cnt }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </x-slot:tabs>

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

    @forelse ($orders as $order)
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-bold text-gray-900">{{ $order->user->name ?? 'Unknown' }}</p>
                    <p class="mt-1 text-xs text-gray-500">Student #{{ $order->user->id ?? '—' }}</p>
                    <p class="text-xs text-gray-500">{{ $order->order_number ?? 'ORD-' . $order->id }}</p>
                    <p class="text-xs text-gray-500">Mode: {{ strtoupper(str_replace('_', ' ', $order->order_mode ?? 'dine_in')) }}</p>
                    <p class="text-xs text-gray-400">{{ $order->created_at?->format('M j, Y g:i A') }}</p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    @php
                        $badge = match ($order->status) {
                            'pending' => 'bg-yellow-100 text-yellow-900',
                            'preparing' => 'bg-orange-100 text-orange-900',
                            'ready' => 'bg-green-100 text-green-900',
                            'completed' => 'bg-green-600 text-white',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold capitalize {{ $badge }}">{{ $order->status }}</span>
                    <a href="{{ route('staff.order.detail', $order) }}"
                        class="text-xs font-semibold text-green-600 hover:text-green-700">View detail</a>
                </div>
            </div>

            <div class="space-y-1 border-t border-gray-100 pt-3 text-sm">
                @forelse ($order->items ?? [] as $item)
                    <div class="flex justify-between gap-2 text-gray-700">
                        <span class="min-w-0">{{ $item->name }} ×{{ $item->qty }}</span>
                        <span class="shrink-0 font-medium">₱{{ number_format((float) $item->price * (int) $item->qty, 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">No line items.</p>
                @endforelse
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 font-bold text-gray-900">
                <span>Total</span>
                <span>₱{{ number_format((float) ($order->total ?? 0), 2) }}</span>
            </div>

            @if ($order->status === 'pending')
                <form method="post" action="{{ route('staff.orders.status', $order) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit"
                        class="w-full min-h-[44px] touch-manipulation rounded-xl bg-yellow-500 py-2.5 text-sm font-semibold text-yellow-950 shadow-sm transition hover:bg-yellow-400">
                        Start preparing
                    </button>
                </form>
            @elseif($order->status === 'preparing')
                <form method="post" action="{{ route('staff.orders.status', $order) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="ready">
                    <button type="submit"
                        class="w-full min-h-[44px] touch-manipulation rounded-xl bg-orange-500 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">
                        Mark ready for pickup
                    </button>
                </form>
            @elseif($order->status === 'ready')
                <form method="post" action="{{ route('staff.orders.status', $order) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit"
                        class="w-full min-h-[44px] touch-manipulation rounded-xl bg-green-600 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                        Mark completed
                    </button>
                </form>
            @endif
        </div>
    @empty
        <p class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 shadow-sm">No orders in this stage.</p>
    @endforelse
</x-layouts.staff-subpage>
