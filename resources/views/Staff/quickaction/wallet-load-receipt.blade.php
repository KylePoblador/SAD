<x-layouts.staff-subpage title="Wallet Load Receipt" :subtitle="$canteenLabel">
    <div class="mx-auto w-full max-w-3xl space-y-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            Wallet load processed successfully. Receipt generated.
        </div>

        <div class="rounded-2xl border border-gray-300 bg-white p-5 shadow-sm print:shadow-none">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-2 border-b border-dashed border-gray-300 pb-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Official receipt</p>
                    <h1 class="text-2xl font-bold text-gray-900">Wallet Load Receipt</h1>
                    <p class="text-xs text-gray-500">Reference #WL-{{ str_pad((string) $walletLoadLog->id, 8, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-right text-xs">
                    <p class="font-semibold text-gray-700">Status</p>
                    <p class="font-bold text-emerald-700">PAID / PROCESSED</p>
                </div>
            </div>

            <div class="grid gap-3 text-sm sm:grid-cols-2 print:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Date & time</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ optional($walletLoadLog->created_at)->format('M d, Y h:i A') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Canteen</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $canteenLabel }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Student name</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $walletLoadLog->student?->name ?? 'Student' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Student ID</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $walletLoadLog->student?->student_id ?? 'No student ID' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Processed by</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $walletLoadLog->staffMember?->name ?? 'Staff' }}</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-emerald-900">Total Amount Loaded</p>
                    <p class="text-3xl font-bold text-emerald-700">₱{{ number_format((float) $walletLoadLog->amount, 2) }}</p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2 print:hidden">
                <a href="{{ route('staff.scan-pay.wallet-load-receipt.download', $walletLoadLog) }}"
                    class="rounded-lg px-4 py-2 text-sm font-bold shadow-sm"
                    style="background:#065f46;color:#ffffff;border:1px solid #064e3b;">
                    Download Receipt (PNG)
                </a>
                <button type="button" onclick="window.print()"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Print
                </button>
                <a href="{{ route('staff.scan-pay') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Scan another
                </a>
            </div>

            <p class="mt-4 text-center text-[11px] text-gray-500">
                This serves as proof of wallet load transaction in CoinMeal.
            </p>
        </div>
    </div>
</x-layouts.staff-subpage>
