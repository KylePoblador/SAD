<x-layouts.student :title="'Order Mode · '.$canteenName" active="none">
    <div class="mx-auto max-w-lg pt-6 sm:pt-12">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">How would you like your order?</h1>
            <p class="mt-2 text-sm text-gray-500">Choose your preference for {{ $canteenName }}</p>
        </div>

        <form action="{{ route('student.canteen.mode', $college) }}" method="post" class="grid gap-4 sm:grid-cols-2">
            @csrf
            
            <button type="submit" name="mode" value="dine_in" class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-transparent bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all hover:-translate-y-1 hover:border-green-500 hover:shadow-md hover:ring-green-500 active:scale-95">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-3xl transition-transform group-hover:scale-110">
                    🍽️
                </div>
                <h3 class="text-lg font-bold text-gray-900">Dine-in</h3>
                <p class="mt-1 text-center text-xs text-gray-500">Eat at the canteen.<br>Includes seat reservation.</p>
            </button>

            <button type="submit" name="mode" value="takeout" class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-transparent bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all hover:-translate-y-1 hover:border-amber-500 hover:shadow-md hover:ring-amber-500 active:scale-95">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-3xl transition-transform group-hover:scale-110">
                    🛍️
                </div>
                <h3 class="text-lg font-bold text-gray-900">Takeout</h3>
                <p class="mt-1 text-center text-xs text-gray-500">Pick up your food.<br>No seat required.</p>
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="{{ route('student.dashboard') }}" class="text-sm font-semibold text-gray-500 underline decoration-gray-300 transition hover:text-gray-800">
                Cancel and return to Dashboard
            </a>
        </div>
    </div>
</x-layouts.student>
