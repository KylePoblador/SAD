<x-layouts.student title="My Cart" active="cart">
    <div class="space-y-4 sm:space-y-5">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 sm:text-xl">My Cart</h1>
                <p class="mt-0.5 text-xs text-gray-500">All your items across canteens in one place.</p>
            </div>
            @if (count($carts) > 0)
                <div class="shrink-0 text-right">
                    <p class="text-xs text-gray-500">{{ $grandCount }} item(s)</p>
                    <p class="text-base font-bold text-green-600">₱{{ number_format($grandTotal, 2) }}</p>
                </div>
            @endif
        </div>

        @if ($errors->has('checkout'))
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 shadow-sm flex items-start gap-3">
                <span class="text-xl shrink-0">⚠️</span>
                <div>
                    <p class="font-bold">Cannot complete checkout</p>
                    <p class="mt-0.5 text-xs text-red-800/90">{{ $errors->first('checkout') }}</p>
                </div>
            </div>
        @endif


        @if (count($carts) > 1)
            <div class="rounded-2xl border border-blue-100 bg-blue-50/90 px-4 py-3 text-xs leading-relaxed text-blue-950">
                <p class="font-semibold text-blue-900">Multiple canteens</p>
                <p class="mt-1">Each canteen is checked out <strong>separately</strong> — you'll get one order and one receipt per canteen.</p>
            </div>
        @endif

        @forelse ($carts as $cart)
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                {{-- Canteen header --}}
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gradient-to-r {{ $cart['orderMode'] === 'dine_in' ? 'from-green-50 to-emerald-50' : 'from-amber-50 to-orange-50' }} px-4 py-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-2xl">{{ $cart['orderMode'] === 'dine_in' ? '🍽️' : '🛍️' }}</span>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 truncate">{{ $cart['label'] }}</p>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                                <span class="rounded-full {{ $cart['orderMode'] === 'dine_in' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }} px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                                    {{ $cart['orderMode'] === 'dine_in' ? 'Dine-in' : 'Takeout' }}
                                </span>
                                @if ($cart['hasReservedSeat'])
                                    <span class="text-[10px] font-semibold text-indigo-700">Seat {{ $cart['reservedSeat'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs text-gray-500">Balance</p>
                        <p class="text-sm font-bold {{ $cart['canCheckout'] ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format($cart['walletBalance'], 2) }}</p>
                    </div>
                </div>

                {{-- Items --}}
                <div class="divide-y divide-gray-50 px-4">
                    @foreach ($cart['lines'] as $line)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $line['name'] }}</p>
                                <p class="text-xs text-gray-500">₱{{ number_format((float) $line['price'], 2) }} × {{ (int) $line['qty'] }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <p class="text-sm font-bold text-gray-800">₱{{ number_format((float) $line['price'] * (int) $line['qty'], 2) }}</p>
                                <form action="{{ route('student.cart.remove', $cart['college']) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="menu_item_id" value="{{ (int) $line['menu_item_id'] }}">
                                    <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500" title="Remove">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="border-t border-gray-100 bg-gray-50/60 px-4 py-3">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-600">Subtotal · {{ $cart['count'] }} item(s)</span>
                        <span class="text-base font-bold text-gray-900">₱{{ number_format($cart['subtotal'], 2) }}</span>
                    </div>
                    @if (! $cart['canCheckout'])
                        <p class="mb-2 text-xs text-red-600">Insufficient balance for this canteen. Please top up first.</p>
                    @endif
                    @if ($cart['orderMode'] === 'dine_in' && ! $cart['hasReservedSeat'])
                        <p class="mb-2 text-xs text-amber-700">No seat reserved — you'll pick a seat at checkout.</p>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('student.cart', $cart['college']) }}"
                            class="inline-flex flex-1 min-h-[44px] touch-manipulation items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Edit cart
                        </a>
                        <form action="{{ route('student.cart.checkout', $cart['college']) }}" method="post" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="min-h-[44px] w-full touch-manipulation rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                @disabled(! $cart['canCheckout'])>
                                {{ $cart['orderMode'] === 'dine_in' ? 'Select Seat & Checkout' : 'Place Order' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">Your cart is empty</p>
                <p class="mt-1 text-xs text-gray-500">Browse a canteen and start adding items.</p>
                <a href="{{ route('student.dashboard') }}"
                    class="mt-4 inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto sm:px-6">
                    Browse canteens
                </a>
            </div>
        @endforelse

        @if (count($carts) > 1)
            <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-600 to-teal-700 p-5 text-white shadow-lg space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold">Total for all canteens</h3>
                        <p class="text-xs text-emerald-100/90">Click below to place all orders simultaneously</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-emerald-100/80">{{ $grandCount }} items total</p>
                        <p class="text-2xl font-black">₱{{ number_format($grandTotal, 2) }}</p>
                    </div>
                </div>
                <form action="{{ route('student.cart.checkout-all') }}" method="post" onsubmit="confirmPlaceAll(event);">
                    @csrf
                    <button type="submit" class="min-h-[48px] w-full touch-manipulation rounded-xl bg-white text-sm font-bold text-emerald-800 shadow-md transition hover:bg-emerald-50 active:scale-95">
                        Place All Orders Now
                    </button>
                </form>
            </div>

            @push('scripts')
            <script>
                async function confirmPlaceAll(e) {
                    if (e) e.preventDefault();
                    const form = e ? e.target : document.querySelector('form[action$="checkout-all"]');
                    const ok = await CoinmealDialog.confirm({
                        title: 'Place all orders?',
                        message: 'This will process orders and deduct coins from all canteens currently in your cart.',
                        variant: 'primary',
                        confirmLabel: 'Yes, place all orders',
                        cancelLabel: 'Go back'
                    });
                    if (ok) {
                        form.submit();
                    }
                }
            </script>
            @endpush
        @endif
    </div>
</x-layouts.student>
