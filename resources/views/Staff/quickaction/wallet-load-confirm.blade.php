<x-layouts.staff-subpage title="Wallet load confirmation" subtitle="Verify cash top-up before crediting">
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <p class="text-xs text-gray-500">Canteen</p>
        <p class="text-lg font-bold">{{ $canteenLabel }}</p>
        <p class="mt-2 text-sm text-gray-600">Student: {{ $student->name ?? 'Unknown' }}</p>
        <p class="text-sm text-gray-600">Amount: ₱{{ number_format((float) $entry->amount, 2) }}</p>
        <p class="text-xs text-gray-500">Token expires {{ $entry->expires_at->format('M d, Y g:i A') }}</p>

        <form method="post" action="{{ route('staff.wallet-load.consume', $entry->token) }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white hover:bg-green-700">
                Confirm wallet load
            </button>
        </form>
    </div>
</x-layouts.staff-subpage>
