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
                <p class="mt-1 text-xs font-semibold {{ ($selectedServiceMode ?? 'dine_in') === 'dine_in' ? 'text-emerald-700' : 'text-blue-700' }}">
                    Mode: {{ ($selectedServiceMode ?? 'dine_in') === 'dine_in' ? 'Dine-in' : 'Take out' }}
                </p>
            </div>
            <a href="{{ route('student.cart.hub') }}"
                class="shrink-0 text-xs font-semibold text-gray-500 underline decoration-gray-300 hover:text-gray-800">
                All carts
            </a>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-900">Change dining mode</p>
                    <p class="mt-1 text-xs text-gray-500">Switch anytime before checkout.</p>
                </div>
                <form method="post" action="{{ route('student.canteen.mode', ['college' => $college]) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <input type="hidden" name="redirect_to" value="cart">
                    <button type="submit" name="service_mode" value="dine_in"
                        class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($selectedServiceMode ?? 'dine_in') === 'dine_in' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        Dine in
                    </button>
                    <button type="submit" name="service_mode" value="takeout"
                        class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($selectedServiceMode ?? 'dine_in') === 'takeout' ? 'bg-blue-600 text-white shadow-sm' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        Take out
                    </button>
                </form>
            </div>
            <p class="mt-3 text-xs {{ ($selectedServiceMode ?? 'dine_in') === 'dine_in' ? 'text-amber-700' : 'text-blue-700' }}">
                {{ ($selectedServiceMode ?? 'dine_in') === 'dine_in'
                    ? 'Seat reservation is required for dine in checkout.'
                    : 'Take out orders can proceed without a seat reservation.' }}
            </p>
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

        @if (($selectedServiceMode ?? 'dine_in') === 'dine_in' && ! $hasReservedSeat)
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
        @if ($hasReservedSeat)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950">
                <p class="font-semibold">Seat reservation active</p>
                <p class="mt-1 text-xs text-emerald-900/90">Need to switch seats or release your seat? You can do it before checkout.</p>
                @if (! empty($activeReservationCode))
                    <p class="mt-1 text-xs font-semibold text-emerald-900/90">Reservation code: {{ $activeReservationCode }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('student.reserve', $college) }}"
                        class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                        Change seat
                    </a>
                    <form action="{{ route('student.cancel.seat', ['college' => $college]) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-red-200 bg-white px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">
                            Cancel reservation
                        </button>
                    </form>
                </div>
            </div>
        @endif
        @if (($selectedServiceMode ?? 'dine_in') === 'dine_in')
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-950">
                <p class="font-semibold">Join existing reservation</p>
                <p class="mt-1 text-xs text-blue-900/90">If your friend already reserved a seat, enter their code here.</p>
                <form method="POST" action="{{ route('student.canteen.join-reservation', ['college' => $college]) }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
                    @csrf
                    <input type="text" name="reservation_code" maxlength="20" required
                        class="w-full rounded-xl border border-blue-200 bg-white px-3 py-2 text-sm uppercase text-blue-900 placeholder:text-blue-400"
                        placeholder="Reservation code" value="{{ old('reservation_code') }}">
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Join
                    </button>
                </form>
                @error('reservation_code')
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
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
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Order mode</label>
                        <select name="service_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="dine_in" @selected(old('service_mode', $selectedServiceMode ?? 'dine_in') === 'dine_in')>Dine in</option>
                            <option value="takeout" @selected(old('service_mode', $selectedServiceMode ?? 'dine_in') === 'takeout')>Take out</option>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-500">Seat is only required for dine in.</p>
                    </div>
                    <button type="submit"
                        class="min-h-[48px] w-full touch-manipulation rounded-xl bg-green-600 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled($walletBalance + 0.001 < $subtotal)>
                        Place order
                    </button>
                </form>
                <p class="mt-2 text-center text-[11px] text-gray-400">This checkout creates <strong>one order</strong> for
                    <strong>{{ $canteenName }}</strong> only. Other canteens’ carts are unchanged and get their own order and receipt when you check out there.</p>
            </div>
        @endif
    </div>
</x-layouts.student>
