@php
    $active = $active ?? 'home';
    $navItem = function (string $tab) use ($active) {
        if ($active === 'none') {
            return 'flex flex-col items-center text-xs text-gray-400';
        }

        return $active === $tab
            ? 'flex flex-col items-center text-xs font-semibold text-green-600'
            : 'flex flex-col items-center text-xs text-gray-400';
    };
    $bottomCartCount = 0;
    foreach (session('student_carts', []) as $_lines) {
        if (is_array($_lines)) {
            $bottomCartCount += (int) collect($_lines)->sum(fn ($l) => (int) ($l['qty'] ?? 0));
        }
    }
@endphp

<div
    class="fixed bottom-0 left-0 right-0 z-10 flex justify-around border-t border-gray-200 bg-white py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:py-3.5">
    <a href="{{ route('student.dashboard') }}" class="{{ $navItem('home') }}">
        Home
    </a>
    <a href="{{ route('student.cart.hub') }}" class="{{ $navItem('cart') }} relative">
        Cart
        @if ($bottomCartCount > 0)
            <span class="absolute -right-2 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-600 text-[8px] font-bold text-white">{{ $bottomCartCount > 99 ? '99+' : $bottomCartCount }}</span>
        @endif
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
