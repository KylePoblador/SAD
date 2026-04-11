<div class="sticky top-0 z-10 border-b border-gray-200 bg-white shadow-sm">
    <div class="coinmeal-container flex items-center justify-between py-3">
        <div class="min-w-0 pr-2">
            <h1 class="text-lg font-bold text-green-600 sm:text-xl">CoinMeal</h1>
            <p class="truncate text-xs text-gray-500 sm:text-sm">University of Southern Mindanao</p>
        </div>
        <div class="flex shrink-0 items-center gap-3 sm:gap-4">
            <a href="{{ route('student.notification') }}" class="relative text-gray-500 hover:text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="unread-badge"
                    class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
                    style="display: none;">0</span>
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 sm:h-6 sm:w-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
        </div>
    </div>
</div>
