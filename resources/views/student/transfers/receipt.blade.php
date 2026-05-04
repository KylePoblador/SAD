<x-layouts.student title="Transfer Receipt" active="wallet">
    @php
        $transfer = $walletTransfer;
        $viewerId = (int) auth()->id();
        $isSender = (int) $transfer->sender_user_id === $viewerId;
        $counterparty = $isSender ? $transfer->receiver : $transfer->sender;
    @endphp

    <div class="overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
        <div class="-mx-5 -mt-5 mb-4 flex flex-wrap items-start justify-between gap-3 border-b border-emerald-300/60 bg-gradient-to-r from-emerald-700 via-green-700 to-emerald-500 px-5 py-4 text-white">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Official receipt</p>
                <h1 class="text-xl font-bold text-white">Wallet Transfer</h1>
                <p class="mt-1 text-xs text-emerald-100">Reference #WT-{{ str_pad((string) $transfer->id, 8, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.wallet.transfer.receipt.download', $transfer) }}"
                    class="rounded-lg bg-white/20 px-3 py-2 text-xs font-bold text-white backdrop-blur hover:bg-white/30">
                    Download (PNG)
                </a>
                <button type="button" onclick="window.print()"
                    class="rounded-lg bg-white/20 px-3 py-2 text-xs font-bold text-white backdrop-blur hover:bg-white/30">
                    Print
                </button>
                <a href="{{ route('student.wallet') }}" class="rounded-lg px-3 py-2 text-xs font-bold backdrop-blur"
                    style="background-color:rgba(6,95,70,0.92);color:#ffffff;border:1px solid rgba(255,255,255,0.65);">
                    Back to wallet
                </a>
            </div>
        </div>

        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-gray-500">Status</p>
                <p class="mt-1 font-semibold text-green-700">Transfer successful</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-gray-500">Date & time</p>
                <p class="mt-1 font-semibold text-gray-800">{{ optional($transfer->created_at)->format('M d, Y h:i A') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-gray-500">Amount</p>
                <p class="mt-1 text-lg font-bold text-gray-900">₱{{ number_format((float) $transfer->amount, 2) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-gray-500">Canteen wallet</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $collegeLabel }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-gray-500">{{ $isSender ? 'Sent to' : 'Received from' }}</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $counterparty?->name ?? 'Student' }}</p>
                @if (!empty($counterparty?->student_id))
                    <p class="text-xs text-gray-500">{{ $counterparty->student_id }}</p>
                @endif
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-gray-500">Role</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $isSender ? 'You sent this transfer' : 'You received this transfer' }}</p>
            </div>
        </div>

        @if (!empty($transfer->note))
            <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-3 text-sm text-blue-900">
                <p class="text-xs font-semibold uppercase tracking-wide">Note</p>
                <p class="mt-1">{{ $transfer->note }}</p>
            </div>
        @endif

        <p class="mt-4 text-center text-[11px] text-gray-500">
            This serves as proof of wallet transfer transaction in CoinMeal.
        </p>
    </div>
</x-layouts.student>
