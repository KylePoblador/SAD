@php
    $status = $status ?? 'pending';
@endphp

<x-layouts.staff-subpage title="Order Management" :subtitle="$canteenName ?? 'Canteen'">
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
                @endphp
                <a href="{{ route('staff.orders', ['status' => $key]) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $status === $key ? $activeTab : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </x-slot:tabs>

    @forelse ($orders as $order)
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-start justify-between gap-2">
                <div>
                    <p class="font-bold text-gray-900">{{ $order->name }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $order->student_id }}</p>
                    <p class="text-xs text-gray-500">Order ID: {{ $order->order_number }}</p>
                </div>
                @if ($status === 'completed')
                    <div class="text-right">
                        <span
                            class="inline-block rounded-full bg-green-600 px-2.5 py-0.5 text-xs font-bold text-white">PAID</span>
                        <p class="mt-1 text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-1 border-t border-gray-100 pt-3 text-sm">
                @if (isset($order->items) && $order->items->count() > 0)
                    @foreach ($order->items as $item)
                        <div class="flex justify-between text-gray-700">
                            <span>{{ $item->name }} ×{{ $item->qty }}</span>
                            <span class="font-medium">₱{{ number_format($item->price, 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="flex justify-between text-gray-700">
                        <span>Chicken Adobo Meal ×1</span>
                        <span class="font-medium">₱65.00</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Iced Coffee ×1</span>
                        <span class="font-medium">₱35.00</span>
                    </div>
                @endif
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 font-bold text-gray-900">
                <span>Total</span>
                <span>₱{{ number_format($order->total ?? 100, 2) }}</span>
            </div>

            @if ($status === 'pending')
                <button type="button"
                    class="mt-4 w-full rounded-xl bg-yellow-500 py-2.5 text-sm font-semibold text-yellow-950 shadow-sm transition hover:bg-yellow-400">
                    Start Preparing
                </button>
            @elseif($status === 'preparing')
                <button type="button"
                    class="mt-4 w-full rounded-xl bg-orange-500 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">
                    Mark as Ready
                </button>
            @elseif($status === 'ready')
                <button type="button"
                    class="mt-4 w-full rounded-xl bg-green-600 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                    Mark as Complete
                </button>
            @endif
        </div>
    @empty
        <p class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 shadow-sm">No orders found.</p>
    @endforelse
</x-layouts.staff-subpage>
