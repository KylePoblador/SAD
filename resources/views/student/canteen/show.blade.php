@php
    $categories = $menuItems->pluck('category')->unique()->filter()->values();
@endphp

<x-layouts.student :title="$canteenName" active="home">
    <div class="space-y-4 sm:space-y-5">
        {{-- Page header --}}
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ $canteenName }}</h1>
                <p class="text-xs font-medium text-gray-500">{{ strtoupper($college) }}</p>
            </div>
            <a href="{{ route('student.cart', $college) }}"
                class="flex shrink-0 flex-col items-end gap-0.5 rounded-lg p-1 transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                aria-label="Open cart">
                <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Cart</span>
                <span id="cart-count"
                    class="flex h-8 min-w-[2rem] items-center justify-center rounded-full bg-gray-800 px-2 text-sm font-bold text-white">{{ (int) ($cartCount ?? 0) }}</span>
            </a>
        </div>

        {{-- Per-canteen wallet --}}
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-600 via-green-600 to-teal-700 p-5 text-white shadow-md ring-1 ring-black/5">
            <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
            <p class="text-sm font-medium text-white/90">Available here · {{ $canteenName }}</p>
            <p class="mt-1 text-4xl font-bold tracking-tight">₱{{ number_format($walletBalance ?? 0, 2) }}</p>
            <p class="mt-2 text-xs leading-relaxed text-white/85">
                Only for <strong class="text-white">{{ $canteenName }}</strong>. Other canteens keep separate balances.
                <strong class="text-white">Wallet</strong> shows your total everywhere.
            </p>
            <div class="mt-4 rounded-xl bg-black/15 px-4 py-3 text-xs leading-relaxed backdrop-blur-sm">
                <p><strong class="text-white">Notify canteen</strong> doesn’t add money until staff <strong
                        class="text-white">confirm load</strong> after you pay cash.</p>
                <p class="mt-2">Top up: <strong class="text-white">Wallet</strong> → <strong class="text-white">Load
                        wallet</strong> → choose <strong class="text-white">{{ $canteenName }}</strong>.</p>
                <a href="{{ route('student.wallet') }}"
                    class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-green-700 shadow-sm transition hover:bg-green-50 sm:w-auto">
                    Go to Wallet
                </a>
            </div>
        </div>

        {{-- Seats --}}
        <div
            class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900">Seat availability</p>
                @if ($hasReservedSeat)
                    <div class="mt-2 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-900">
                        Reserved seat: <strong>{{ $reservedSeat }}</strong>
                    </div>
                @endif
                @if (session('seat'))
                    <div class="mt-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-900">
                        Seat <strong>{{ session('seat') }}</strong> saved.
                    </div>
                @endif
                <p class="mt-2 text-xs text-gray-500">{{ $availableSeats }}/{{ $totalSeats }} seats available</p>
            </div>
            <a href="{{ route('student.reserve', $college) }}"
                class="inline-flex shrink-0 items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                Reserve seat
            </a>
        </div>

        {{-- Menu --}}
        <div>
            <h2 class="mb-3 text-base font-bold text-gray-800">Menu</h2>

            @if (! $hasReservedSeat && $menuItems->isNotEmpty())
                <div
                    class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm">
                    <p class="font-semibold">Reserve a seat to add items</p>
                    <p class="mt-1 text-xs leading-relaxed text-amber-900/90">You need an active seat reservation at this
                        canteen before you can add food to your cart or check out.</p>
                    <a href="{{ route('student.reserve', $college) }}"
                        class="mt-3 inline-flex w-full min-h-[44px] items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700 touch-manipulation sm:w-auto">
                        Reserve seat
                    </a>
                </div>
            @endif

            @if ($errors->has('cart'))
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    {{ $errors->first('cart') }}
                </div>
            @endif

            @if ($menuItems->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-600">
                    No menu items yet. Staff will add items here.
                </div>
            @else
                @if ($categories->isNotEmpty())
                    <div class="mb-3">
                        <p class="mb-2 text-xs font-semibold text-gray-500">Category</p>
                        <div class="flex gap-2 overflow-x-auto pb-1 [-webkit-overflow-scrolling:touch] sm:flex-wrap">
                            <button type="button"
                                class="category-btn shrink-0 rounded-full bg-green-600 px-3 py-1.5 text-xs font-semibold text-white"
                                data-active="1" onclick="filterCategory('All', this)">All</button>
                            @foreach ($categories as $cat)
                                <button type="button"
                                    class="category-btn shrink-0 rounded-full bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800"
                                    onclick="filterCategory('{{ e($cat) }}', this)">{{ $cat }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <p class="mb-2 text-xs font-semibold text-gray-500">Price range</p>
                    <div class="flex gap-2 overflow-x-auto pb-1 [-webkit-overflow-scrolling:touch] sm:flex-wrap">
                        <button type="button"
                            class="price-btn shrink-0 rounded-full bg-green-600 px-3 py-1.5 text-xs font-semibold text-white"
                            data-active="1" onclick="filterPrice('all', this)">All</button>
                        <button type="button"
                            class="price-btn shrink-0 rounded-full bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800"
                            onclick="filterPrice('1-50', this)">₱1–50</button>
                        <button type="button"
                            class="price-btn shrink-0 rounded-full bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800"
                            onclick="filterPrice('51-100', this)">₱51–100</button>
                        <button type="button"
                            class="price-btn shrink-0 rounded-full bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800"
                            onclick="filterPrice('101-150', this)">₱101–150</button>
                        <button type="button"
                            class="price-btn shrink-0 rounded-full bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800"
                            onclick="filterPrice('151+', this)">₱151+</button>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($menuItems as $item)
                        <div class="menu-item-row flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                            data-category="{{ e($item->category) }}" data-price="{{ $item->price }}">
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($item->imagePublicUrl())
                                    <img src="{{ $item->imagePublicUrl() }}" alt=""
                                        class="h-16 w-16 shrink-0 rounded-xl object-cover ring-1 ring-gray-100">
                                @else
                                    <div
                                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs text-gray-400">
                                        —
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->category }}</p>
                                </div>
                            </div>
                            <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:items-end">
                                @if ($hasReservedSeat)
                                    <button type="button"
                                        class="add-cart-btn min-h-[44px] w-full touch-manipulation rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 active:bg-green-800 sm:min-h-0 sm:w-auto sm:px-4 sm:py-2"
                                        data-menu-item-id="{{ $item->id }}"
                                        onclick="addToCart(this)">
                                        Add to cart
                                    </button>
                                @else
                                    <a href="{{ route('student.reserve', $college) }}"
                                        class="inline-flex min-h-[44px] w-full touch-manipulation items-center justify-center rounded-xl border-2 border-amber-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-amber-800 transition hover:bg-amber-50 sm:min-h-0 sm:w-auto sm:px-4 sm:py-2">
                                        Reserve seat to order
                                    </a>
                                @endif
                                <p class="text-center text-base font-bold text-green-600 sm:text-right">
                                    ₱{{ number_format($item->price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            let selectedCategory = "All";
            let selectedPrice = "all";

            const pillActive = ["bg-green-600", "text-white"];
            const pillIdle = ["bg-gray-200", "text-gray-800"];

            function setPillGroup(selector, activeBtn) {
                document.querySelectorAll(selector).forEach(b => {
                    pillActive.forEach(c => b.classList.remove(c));
                    pillIdle.forEach(c => b.classList.add(c));
                });
                pillIdle.forEach(c => activeBtn.classList.remove(c));
                pillActive.forEach(c => activeBtn.classList.add(c));
            }

            function filterCategory(category, btn) {
                selectedCategory = category;
                setPillGroup(".category-btn", btn);
                applyFilters();
            }

            function filterPrice(price, btn) {
                selectedPrice = price;
                setPillGroup(".price-btn", btn);
                applyFilters();
            }

            function applyFilters() {
                document.querySelectorAll(".menu-item-row").forEach(item => {
                    const category = (item.getAttribute("data-category") || "").toLowerCase();
                    const price = parseFloat(item.getAttribute("data-price"));
                    const categoryMatch = selectedCategory === "All" || category === selectedCategory.toLowerCase();
                    let priceMatch = false;
                    switch (selectedPrice) {
                        case "all":
                            priceMatch = true;
                            break;
                        case "1-50":
                            priceMatch = price >= 1 && price <= 50;
                            break;
                        case "51-100":
                            priceMatch = price >= 51 && price <= 100;
                            break;
                        case "101-150":
                            priceMatch = price >= 101 && price <= 150;
                            break;
                        case "151+":
                            priceMatch = price >= 151;
                            break;
                    }
                    item.classList.toggle("hidden", !(categoryMatch && priceMatch));
                });
            }

            const cartAddUrl = @json($cartAddUrl ?? '');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            async function addToCart(btn) {
                const id = btn?.getAttribute("data-menu-item-id");
                if (!id || !cartAddUrl) return;
                btn.disabled = true;
                try {
                    const res = await fetch(cartAddUrl, {
                        method: "POST",
                        credentials: "same-origin",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrf,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({
                            menu_item_id: Number(id)
                        }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        alert(data.message || "Could not add to cart.");
                        return;
                    }
                    if (typeof data.cart_count === "number") {
                        const el = document.getElementById("cart-count");
                        if (el) el.textContent = String(data.cart_count);
                    }
                    const label = btn.textContent;
                    btn.textContent = "Added!";
                    setTimeout(() => {
                        btn.textContent = label;
                    }, 1200);
                } catch (e) {
                    alert("Network error. Try again.");
                } finally {
                    btn.disabled = false;
                }
            }
        </script>
    @endpush
</x-layouts.student>
