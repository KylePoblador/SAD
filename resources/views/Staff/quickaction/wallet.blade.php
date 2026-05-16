<x-layouts.staff-subpage title="Student wallet management" :subtitle="$canteenName" :wide="true">

    @if (session('status'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @php
        $historyList = $walletLoadHistory ?? collect();
    @endphp

    <div
        class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50/60 p-5 shadow-sm ring-1 ring-emerald-100/80">
        <h2 class="text-lg font-bold text-gray-900">Load wallets with QR</h2>
        <p class="mt-2 text-sm leading-relaxed text-gray-600">
            Students tap <strong>Load wallet</strong> on their phone, enter the amount and canteen, then show you a QR code.
            Use the same <strong>QR scanner</strong> as for food orders — it opens the correct confirmation screen automatically.
        </p>
        <a href="{{ route('staff.qr.scanner') }}"
            class="mt-4 inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:from-emerald-700 hover:to-teal-700 sm:w-auto">
            Open QR scanner
        </a>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Recent loads</h2>
                <p class="mt-1 text-xs text-gray-500">{{ $historyList->count() }} wallet credit{{ $historyList->count() !== 1 ? 's' : '' }} at this canteen.</p>
            </div>
            <button type="button" onclick="openWalletHistoryModal()"
                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-900 transition hover:bg-amber-100">
                Full history
            </button>
        </div>

        @if ($historyList->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                <p class="text-sm text-gray-600">No wallet loads recorded yet for this canteen.</p>
                <p class="mt-1 text-xs text-gray-500">They appear here after staff confirm a wallet QR.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100 px-4 py-2 sm:px-6">
                @foreach ($historyList->take(12) as $log)
                    @php
                        $stu = $log->student;
                        $stf = $log->staffMember;
                    @endphp
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $stu->name ?? 'Student' }}
                                @if (!empty($stu->student_id))
                                    <span class="font-mono text-xs text-gray-500">· {{ $stu->student_id }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">{{ $stf->name ?? 'Staff' }} ·
                                {{ $log->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="text-base font-bold text-amber-700">+₱{{ number_format($log->amount, 2) }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div id="wallet-history-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"
        role="dialog" aria-modal="true" aria-labelledby="wallet-history-title">
        <div class="max-h-[85vh] w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-5">
                <h2 id="wallet-history-title" class="text-base font-bold text-gray-900">Wallet top-up history</h2>
                <button type="button" onclick="closeWalletHistoryModal()"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-[calc(85vh-3.5rem)] overflow-y-auto px-4 py-3 sm:px-5">
                @if ($historyList->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-600">No loads recorded yet for this canteen.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($historyList as $log)
                            @php
                                $stu = $log->student;
                                $stf = $log->staffMember;
                            @endphp
                            <li
                                class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-100/90 bg-amber-50/40 px-3 py-3 text-sm">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $stu->name ?? 'Student' }}
                                        @if (!empty($stu->student_id))
                                            <span class="font-mono text-xs text-gray-500">· {{ $stu->student_id }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">By {{ $stf->name ?? 'Staff' }} ·
                                        {{ $log->created_at->diffForHumans() }}</p>
                                </div>
                                <p class="text-base font-bold text-amber-700">+₱{{ number_format($log->amount, 2) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <script>
        function openWalletHistoryModal() {
            const el = document.getElementById('wallet-history-modal');
            if (el) {
                el.classList.remove('hidden');
                el.classList.add('flex');
            }
        }

        function closeWalletHistoryModal() {
            const el = document.getElementById('wallet-history-modal');
            if (el) {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        }

        document.getElementById('wallet-history-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeWalletHistoryModal();
            }
        });
    </script>
</x-layouts.staff-subpage>
