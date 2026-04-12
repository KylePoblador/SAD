<x-layouts.student title="Cart" active="cart">
    <div class="space-y-4 sm:space-y-5">
        <div>
            <h1 class="text-lg font-bold text-gray-900 sm:text-xl">Your cart</h1>
            <p class="mt-1 text-xs text-gray-500 sm:text-sm">Open a canteen cart to review items and check out.</p>
        </div>

        @forelse ($carts as $row)
            <a href="{{ route('student.cart', $row['college']) }}"
                class="flex min-h-[44px] touch-manipulation items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-green-200 hover:bg-green-50/40">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900">{{ $row['label'] }}</p>
                    <p class="text-xs text-gray-500">{{ $row['count'] }} item(s)</p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-sm font-bold text-green-600">₱{{ number_format($row['subtotal'], 2) }}</p>
                    <p class="text-xs font-medium text-green-700">View</p>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-10 text-center shadow-sm">
                <p class="text-sm font-medium text-gray-700">Your cart is empty</p>
                <p class="mt-1 text-xs text-gray-500">Browse a canteen on Home and add items after you reserve a seat.</p>
                <a href="{{ route('student.dashboard') }}"
                    class="mt-4 inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto sm:px-6">
                    Go to Home
                </a>
            </div>
        @endforelse
    </div>
</x-layouts.student>
