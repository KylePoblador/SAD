<x-layouts.staff-subpage title="Student wallet management" :subtitle="$canteenName" :wide="true">

        @if (session('status') === 'wallet-load-processed')
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                Wallet load confirmed successfully.
            </div>
        @endif

        @php
            $historyList = $walletLoadHistory ?? collect();
        @endphp
        <div class="mb-6 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-gray-900">QR Wallet Load</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('staff.scan-pay') }}"
                        class="rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-900 hover:bg-green-100">
                        Open QR scanner
                    </a>
                </div>
            </div>
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/90 px-4 py-5 text-center text-sm text-gray-600">
                Scan the student's wallet-load QR, review amount/name on scanner page, then confirm.
            </div>
        </div>

        {{-- WALLET LOAD HISTORY --}}
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-bold text-gray-900">Wallet Load History</h2>
                        <p class="mt-1 text-xs text-gray-500">Total rows: {{ $historyList->count() }}</p>
                        <p class="mt-2 text-xs text-gray-600">All confirmed wallet loads from QR scans appear here.</p>
                    </div>
                    <div class="w-full shrink-0 lg:max-w-sm">
                        <label for="history-search-input" class="mb-1 block text-xs font-semibold text-gray-600">Search by name or ID</label>
                        <input type="search" id="history-search-input" placeholder="Name or student ID…" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30">
                    </div>
                </div>
            </div>
            @if($historyList->isEmpty())
                <div class="p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h6m4 0a2 2 0 00-2-2h-6a2 2 0 00-2 2m10 0v6a2 2 0 01-2 2h-6a2 2 0 01-2-2" />
                    </svg>
                    <p class="text-lg font-medium text-gray-600">No wallet-load history yet</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">Confirmed QR loads will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Student</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Student ID</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Processed by</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Amount</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historyList as $log)
                                @php
                                    $stu = $log->student;
                                    $stf = $log->staffMember;
                                @endphp
                                <tr class="history-row border-b border-gray-200 transition hover:bg-gray-50"
                                    data-search="{{ strtolower(($stu->name ?? 'student').' '.($stu->student_id ?? '').' '.($stf->name ?? 'staff')) }}">
                                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $stu->name ?? 'Student' }}</td>
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">{{ $stu->student_id ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $stf->name ?? 'Staff' }}</td>
                                    <td class="px-6 py-4 text-xs text-gray-500">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-6 py-4 text-right text-base font-bold text-amber-700">+₱{{ number_format($log->amount, 2) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('staff.scan-pay.wallet-load-receipt', $log) }}"
                                            class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
                                            View receipt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    <script>
        document.getElementById('history-search-input')?.addEventListener('input', function(e) {
            const q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('tbody tr.history-row[data-search]').forEach(function(row) {
                const hay = row.getAttribute('data-search') || '';
                row.style.display = !q || hay.includes(q) ? '' : 'none';
            });
        });
    </script>

</x-layouts.staff-subpage>
