@php
    $active = $active ?? 'home';
    $studentCarts = session('student_carts', []);
    $cartCount = (int) collect($studentCarts)->flatten(1)->sum(fn ($line) => (int) ($line['qty'] ?? 0));
    $navItem = function (string $tab) use ($active) {
        if ($active === 'none') {
            return 'flex flex-col items-center text-xs text-gray-400';
        }

        return $active === $tab
            ? 'flex flex-col items-center text-xs font-semibold text-green-600'
            : 'flex flex-col items-center text-xs text-gray-400';
    };
@endphp

<div
    class="fixed bottom-0 left-0 right-0 z-10 flex justify-around border-t border-gray-200 bg-white py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:py-3.5">
    <a href="{{ route('student.dashboard') }}" class="{{ $navItem('home') }}">
        Home
    </a>
    <a href="{{ route('student.cart.hub') }}" class="relative {{ $navItem('cart') }}">
        Cart
        <span id="bottom-cart-badge"
            class="absolute -right-3 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white shadow transition-transform {{ $cartCount > 0 ? '' : 'hidden' }}">
            {{ $cartCount > 99 ? '99+' : $cartCount }}
        </span>
    </a>
    <a href="{{ route('student.orders') }}" class="{{ $navItem('orders') }}">
        Orders
    </a>
    <a href="{{ route('student.wallet') }}" class="{{ $navItem('wallet') }}">
        Wallet
    </a>
    <a href="{{ route('student.profile') }}" class="{{ $navItem('profile') }}">
        Profile
    </a>
</div>
