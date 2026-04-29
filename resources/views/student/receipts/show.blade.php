<x-layouts.student title="Payment receipt" active="wallet">
    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Payment receipt</h1>
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                Print / Download PDF
            </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Receipt #{{ $receipt->receipt_number }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs text-gray-500">Student</p>
                <p class="font-semibold text-gray-900">{{ $studentName }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs text-gray-500">Canteen</p>
                <p class="font-semibold text-gray-900">{{ $canteenLabel }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs text-gray-500">Paid at</p>
                <p class="font-semibold text-gray-900">{{ ($receipt->paid_at ?? $receipt->created_at)?->format('M d, Y h:i A') }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs text-gray-500">Amount</p>
                <p class="font-semibold text-green-700">PHP {{ number_format((float) $receipt->amount, 2) }}</p>
            </div>
        </div>

        @if ($order)
            <div class="mt-5 rounded-lg border border-gray-100 p-4">
                <p class="text-xs text-gray-500">Order</p>
                <p class="font-semibold text-gray-900">{{ $order->order_number ?? ('ORD-'.$order->id) }}</p>
                <a href="{{ route('student.orders.receipt', $order) }}" class="mt-2 inline-block text-xs font-semibold text-indigo-700 underline">
                    Open order receipt details
                </a>
            </div>
        @endif
    </div>
</x-layouts.student>
