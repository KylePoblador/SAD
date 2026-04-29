<x-layouts.staff-subpage title="QR payment confirmation" subtitle="Verify token before completion">
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <p class="text-xs text-gray-500">Order</p>
        <p class="text-lg font-bold">{{ $order->order_number ?? ('ORD-'.$order->id) }}</p>
        <p class="text-sm text-gray-600">Student: {{ $order->user->name ?? 'Unknown' }}</p>
        <p class="text-sm text-gray-600">Amount: ₱{{ number_format((float) ($order->payable_total ?? $order->total), 2) }}</p>
        <p class="text-xs text-gray-500">Token expires {{ $entry->expires_at->format('M d, Y g:i A') }}</p>

        <form method="post" action="{{ route('staff.qr.consume', $entry->token) }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white hover:bg-green-700">
                Confirm payment and complete order
            </button>
        </form>
    </div>
</x-layouts.staff-subpage>
