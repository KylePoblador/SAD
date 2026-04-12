<x-layouts.student :title="'Cart · '.$canteenName" active="cart">
    <div class="space-y-4 sm:space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('student.canteen', $college) }}"
                    class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to menu
                </a>
                <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ $canteenName }}</h1>
                <p class="text-xs text-gray-500">Cart · {{ strtoupper($college) }}</p>
            </div>
            <a href="{{ route('student.cart.hub') }}"
                class="shrink-0 text-xs font-semibold text-gray-500 underline decoration-gray-300 hover:text-gray-800">
                All carts
            </a>
        </div>

        @if ($errors->has('checkout'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                {{ $errors->first('checkout') }}
            </div>
        @endif

        <div
            class="rounded-2xl border border-gray-100 bg-gradient-to-br from-green-600 to-teal-700 p-4 text-white shadow-sm">
            <p class="text-xs font-medium text-white/90">Pay from this canteen’s wallet</p>
            <p class="mt-1 text-2xl font-bold sm:text-3xl">₱{{ number_format($walletBalance, 2) }}</p>
            <a href="{{ route('student.wallet') }}"
                class="mt-3 inline-flex text-xs font-semibold text-white/95 underline decoration-white/50 hover:text-white">
                Top up wallet
            </a>
        </div>

        @if (! $hasReservedSeat)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <p class="font-semibold">Reserve a seat to check out</p>
                <p class="mt-1 text-xs text-amber-900/90">You can still edit your cart; checkout runs after you have a
                    seat here.</p>
                <a href="{{ route('student.reserve', $college) }}"
                    class="mt-3 inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700 sm:w-auto">
                    Reserve seat
                </a>
            </div>
        @endif

        @if ($lines === [])
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-10 text-center shadow-sm">
                <p class="text-sm text-gray-600">No items in this cart yet.</p>
                <a href="{{ route('student.canteen', $college) }}"
                    class="mt-4 inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700 sm:mx-auto sm:w-auto sm:px-8">
                    Browse menu
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($lines as $line)
                    <div
                        class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $line['name'] }}</p>
                            <p class="text-xs text-gray-500">₱{{ number_format((float) $line['price'], 2) }} each</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                            <form action="{{ route('student.cart.qty', $college) }}" method="post"
                                class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="hidden" name="menu_item_id" value="{{ (int) $line['menu_item_id'] }}">
                                <label class="sr-only" for="qty-{{ (int) $line['menu_item_id'] }}">Quantity</label>
                                <input id="qty-{{ (int) $line['menu_item_id'] }}" name="qty" type="number" min="1"
                                    max="99" value="{{ (int) $line['qty'] }}"
                                    class="w-20 rounded-lg border border-gray-200 px-2 py-2 text-center text-sm font-medium">
                                <button type="submit"
                                    class="min-h-[44px] touch-manipulation rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-100 sm:min-h-0 sm:py-1.5">
                                    Update
                                </button>
                            </form>
                            <form action="{{ route('student.cart.remove', $college) }}" method="post">
                                @csrf
                                <input type="hidden" name="menu_item_id" value="{{ (int) $line['menu_item_id'] }}">
                                <button type="submit"
                                    class="min-h-[44px] touch-manipulation rounded-xl px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 sm:min-h-0 sm:py-1.5">
                                    Remove
                                </button>
                            </form>
                        </div>
                        <p class="text-right text-base font-bold text-green-600 sm:min-w-[6rem]">
                            ₱{{ number_format((float) $line['price'] * (int) $line['qty'], 2) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="text-lg font-bold text-gray-900">₱{{ number_format($subtotal, 2) }}</span>
                </div>
                @if ($walletBalance + 0.001 < $subtotal)
                    <p class="mt-2 text-xs text-red-600">Balance here is lower than the cart total. Top up this
                        canteen’s wallet before checking out.</p>
                @endif

                <form action="{{ route('student.cart.checkout', $college) }}" method="post" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="min-h-[48px] w-full touch-manipulation rounded-xl bg-green-600 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(! $hasReservedSeat || ($walletBalance + 0.001 < $subtotal))>
                        Place order
                    </button>
                </form>
                <p class="mt-2 text-center text-[11px] text-gray-400">Total is charged to this canteen’s wallet only.
                </p>
            </div>
        @endif
    </div>
</x-layouts.student>
