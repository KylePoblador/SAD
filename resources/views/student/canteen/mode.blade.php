<x-layouts.student :title="'Order mode · '.$canteenName" active="home">
    <div class="mx-auto max-w-xl space-y-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h1 class="text-lg font-bold text-gray-900">{{ $canteenName }}</h1>
            <p class="mt-1 text-sm text-gray-600">Choose your order mode first.</p>

            <form method="POST" action="{{ route('student.canteen.mode', $college) }}" class="mt-4 space-y-3">
                @csrf
                <button type="submit" name="service_mode" value="dine_in"
                    class="w-full rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-left text-sm font-semibold text-emerald-900 hover:bg-emerald-100">
                    Dine-in
                    <span class="mt-1 block text-xs font-normal text-emerald-800">Seat reservation required before ordering.</span>
                </button>
                <button type="submit" name="service_mode" value="takeout"
                    class="w-full rounded-xl border border-blue-300 bg-blue-50 px-4 py-3 text-left text-sm font-semibold text-blue-900 hover:bg-blue-100">
                    Take out
                    <span class="mt-1 block text-xs font-normal text-blue-800">No seat reservation needed.</span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.student>
