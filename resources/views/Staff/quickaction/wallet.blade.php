<x-layouts.staff-subpage title="Student wallet management" :subtitle="$canteenName" :wide="true">

        @if (session('status') === 'deposit-inquiry-completed')
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                Deposit inquiry marked as done.
            </div>
        @endif
        @if (session('status') === 'deposit-inquiry-already-done')
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                That inquiry was already processed.
            </div>
        @endif

        @php
            $depositList = $depositInquiries ?? collect();
            $historyList = $walletLoadHistory ?? collect();
        @endphp
        <div class="mb-6 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-gray-900">Pending wallet deposit inquiries</h2>
                @if ($depositList->isNotEmpty())
                    <span
                        class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800">{{ $depositList->count() }}</span>
                @endif
            </div>
            <p class="text-xs text-gray-500">Students who chose <strong>Notify canteen</strong> on their Wallet. When you
                <strong>Confirm load</strong> below, their inquiry closes automatically.</p>
            @if ($depositList->isEmpty())
                <div
                    class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/90 px-4 py-5 text-center text-sm text-gray-600">
                    <p class="font-medium text-gray-700">No pending inquiries.</p>
                    <p class="mt-1 text-xs text-gray-500">They will appear here when a student submits a top-up request.
                    </p>
                </div>
            @else
                <div class="grid gap-3 sm:grid-cols-1 lg:grid-cols-2">
                    @foreach ($depositList as $inquiry)
                        @php
                            $su = $inquiry->user;
                        @endphp
                        <div
                            class="rounded-2xl border border-rose-100/90 bg-white p-4 shadow-sm ring-1 ring-rose-50/80">
                            <div class="min-w-0 flex-1 space-y-1">
                                <p class="text-sm font-bold text-gray-900">{{ $su->name ?? 'Student' }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $su->email ?? '' }}</p>
                                @if (!empty($su->student_id))
                                    <p class="text-xs text-gray-600">Student ID: <span
                                            class="font-semibold">{{ $su->student_id }}</span></p>
                                @endif
                                @if ($inquiry->intended_amount !== null)
                                    <p class="text-sm text-gray-800">Planned: <span
                                            class="font-semibold">₱{{ number_format($inquiry->intended_amount, 2) }}</span>
                                    </p>
                                @endif
                                @if (!empty($inquiry->note))
                                    <p class="text-xs text-gray-600">Note: {{ $inquiry->note }}</p>
                                @endif
                                <p class="text-[11px] text-gray-400">{{ $inquiry->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- LOAD STUDENT WALLET --}}
        <div id="load-wallet" class="hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Load Student Wallet</h2>
                <button onclick="closeLoadWallet()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Student Card --}}
            <div class="bg-white border-2 border-green-200 rounded-2xl p-8 text-center mb-8">
                <div class="flex justify-center mb-6">
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 mb-2" id="load-name">Student Name</h3>
                <p class="text-gray-600 mb-6" id="load-id">Student ID: #0000</p>
            </div>

            {{-- Amount to Load --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Amount to Load</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-lg font-semibold">₱</span>
                    <input type="number" id="load-amount" min="0" step="1" placeholder="0.00"
                        class="w-full pl-8 pr-4 py-4 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg">
                </div>
            </div>

            {{-- Quick Select Buttons --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Quick Select</label>
                <div class="grid grid-cols-3 gap-3">
                    <button onclick="setLoadAmount(50)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱50
                    </button>
                    <button onclick="setLoadAmount(100)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱100
                    </button>
                    <button onclick="setLoadAmount(200)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱200
                    </button>
                    <button onclick="setLoadAmount(500)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱500
                    </button>
                    <button onclick="setLoadAmount(1000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱1000
                    </button>
                    <button onclick="setLoadAmount(2000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱2000
                    </button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-2 gap-4">
                <button onclick="closeLoadWallet()"
                    class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg transition">
                    Cancel
                </button>
                <button onclick="confirmLoad()"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition">
                    Confirm Load
                </button>
            </div>
        </div>

        {{-- STUDENTS LIST --}}
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-gray-900">Select student</h2>
                            <button type="button" onclick="openWalletHistoryModal()"
                                class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 transition hover:bg-amber-100">
                                View history
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Total rows: {{ $students->count() }}</p>
                        <p class="mt-2 text-xs text-gray-600">Assigned students can load anytime. Others stay on the list
                            after a completed request until they send a <strong>new</strong> deposit request — then
                            <strong>Load wallet</strong> is enabled again.</p>
                    </div>
                    <div class="w-full shrink-0 lg:max-w-sm">
                        <label for="search-input" class="mb-1 block text-xs font-semibold text-gray-600">Search by name
                            or ID</label>
                        <input type="search" id="search-input" placeholder="Name or student ID…" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30">
                    </div>
                </div>
            </div>

            @if($students->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Student ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Deposit status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr class="student-row border-b border-gray-200 transition hover:bg-gray-50 {{ !empty($student->inquiry_history_only) ? 'bg-slate-50/80' : '' }}"
                            data-user-id="{{ $student->id }}"
                            data-search="{{ strtolower($student->name . ' ' . ($student->student_id ?? '') . ' ' . $student->id) }}">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800">{{ $student->name }}</p>
                                @if (!empty($student->has_pending_inquiry))
                                    <span
                                        class="mt-1 inline-block rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-900">Pending
                                        deposit</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm text-gray-600">
                                    @if (!empty($student->student_id))
                                        {{ $student->student_id }}
                                    @else
                                        #{{ $student->id }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @if (!empty($student->has_pending_inquiry))
                                    <span class="text-xs font-semibold text-amber-800">Awaiting load</span>
                                @elseif(!empty($student->last_completed_inquiry_at))
                                    <span class="text-xs text-slate-600">Last request completed</span>
                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        {{ \Illuminate\Support\Carbon::parse($student->last_completed_inquiry_at)->diffForHumans() }}
                                    </p>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if (!empty($student->can_load_wallet))
                                    {{-- Single-quoted onclick + double-quoted PHP keys: @json uses "; 'id' would end the HTML attribute --}}
                                    <button type="button"
                                        onclick='openLoadWallet(@json(["id" => $student->id, "name" => $student->name, "student_id" => $student->student_id]))'
                                        class="rounded-lg bg-green-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-600">
                                        Load wallet
                                    </button>
                                @else
                                    <button type="button" disabled
                                        class="cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-500"
                                        title="Student must submit a new Notify canteen request from Wallet">
                                        Awaiting request
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3.667A1.667 1.667 0 012 18.333V5.667C2 4.747 2.747 4 3.667 4h10.666c.92 0 1.667.747 1.667 1.667v12.666c0 .92-.747 1.667-1.667 1.667z" />
                </svg>
                <p class="text-gray-600 text-lg font-medium">No students in this list yet</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">Students appear when (1) their profile
                    <strong>College</strong> matches this canteen, or (2) they submit a <strong>Notify canteen</strong>
                    request from Wallet for this canteen. If you see inquiries above but no rows here, ask the student to
                    set the correct college on <strong>Profile</strong> or submit the deposit request again.</p>
            </div>
            @endif
        </div>

        {{-- Confirm load (replaces browser confirm()) --}}
        <div id="load-confirm-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4"
            role="dialog" aria-modal="true" aria-labelledby="load-confirm-title">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 id="load-confirm-title" class="text-lg font-bold text-gray-900">Confirm wallet load</h2>
                    <p id="load-confirm-message" class="mt-2 text-sm leading-relaxed text-gray-600"></p>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50/80 px-5 py-4 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" onclick="closeLoadConfirmModal()"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" onclick="submitConfirmedLoad()"
                        class="rounded-lg bg-green-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-600">
                        Yes, load wallet
                    </button>
                </div>
            </div>
        </div>

        {{-- Success / error / validation messages --}}
        <div id="wallet-feedback-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4"
            role="dialog" aria-modal="true" aria-labelledby="wallet-feedback-title">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="px-5 py-4">
                    <h2 id="wallet-feedback-title" class="text-lg font-bold text-gray-900"></h2>
                    <p id="wallet-feedback-body" class="mt-2 text-sm leading-relaxed text-gray-600"></p>
                </div>
                <div class="border-t border-gray-100 bg-gray-50/80 px-5 py-4 text-right">
                    <button type="button" onclick="closeWalletFeedbackModal()"
                        class="rounded-lg bg-green-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-600">
                        OK
                    </button>
                </div>
            </div>
        </div>

        <div id="wallet-history-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"
            role="dialog" aria-modal="true" aria-labelledby="wallet-history-title">
            <div class="max-h-[85vh] w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 sm:px-5">
                    <h2 id="wallet-history-title" class="text-base font-bold text-gray-900">Wallet top-up history</h2>
                    <button type="button" onclick="closeWalletHistoryModal()"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
        let selectedStudent = null;
        const walletUpdateUrl = (id) => @json(url('/student/wallet/update')) + '/' + id;

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

        let pendingLoadAmount = null;
        let walletFeedbackReload = false;

        function openLoadConfirmModal(amount) {
            pendingLoadAmount = amount;
            const msg = document.getElementById('load-confirm-message');
            if (msg && selectedStudent) {
                msg.textContent =
                    'Load ₱' + amount.toFixed(2) + ' to ' + selectedStudent.name + '\'s wallet?';
            }
            const el = document.getElementById('load-confirm-modal');
            if (el) {
                el.classList.remove('hidden');
                el.classList.add('flex');
            }
        }

        function closeLoadConfirmModal() {
            pendingLoadAmount = null;
            const el = document.getElementById('load-confirm-modal');
            if (el) {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        }

        document.getElementById('load-confirm-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoadConfirmModal();
            }
        });

        function openWalletFeedbackModal(title, body, reloadOnClose) {
            document.getElementById('wallet-feedback-title').textContent = title;
            document.getElementById('wallet-feedback-body').textContent = body;
            walletFeedbackReload = !!reloadOnClose;
            const el = document.getElementById('wallet-feedback-modal');
            if (el) {
                el.classList.remove('hidden');
                el.classList.add('flex');
            }
        }

        function closeWalletFeedbackModal() {
            const el = document.getElementById('wallet-feedback-modal');
            if (el) {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
            if (walletFeedbackReload) {
                window.location.reload();
            }
            walletFeedbackReload = false;
        }

        document.getElementById('wallet-feedback-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeWalletFeedbackModal();
            }
        });

        function openLoadWallet(student) {
            selectedStudent = student;

            // Populate student details
            document.getElementById('load-name').textContent = student.name;
            const idLabel = student.student_id
                ? ('USM ID: ' + student.student_id)
                : ('Account #' + student.id);
            document.getElementById('load-id').textContent = idLabel;

            // Clear input
            document.getElementById('load-amount').value = '';

            // Show load wallet panel
            document.getElementById('load-wallet').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeLoadWallet() {
            document.getElementById('load-wallet').classList.add('hidden');
            selectedStudent = null;
            document.getElementById('load-amount').value = '';
        }

        function setLoadAmount(amount) {
            document.getElementById('load-amount').value = amount;
        }

        function confirmLoad() {
            const amount = parseFloat(document.getElementById('load-amount').value);

            if (!selectedStudent) {
                openWalletFeedbackModal('Select a student', 'Please choose a student from the list first.', false);
                return;
            }

            if (!amount || amount <= 0) {
                openWalletFeedbackModal('Invalid amount', 'Enter an amount greater than zero.', false);
                return;
            }

            openLoadConfirmModal(amount);
        }

        function submitConfirmedLoad() {
            const amount = pendingLoadAmount;
            const student = selectedStudent;
            closeLoadConfirmModal();
            if (!amount || !student) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch(walletUpdateUrl(student.id), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ amount: amount }),
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    let msg = data.error || data.message || 'Request failed (' + response.status + ')';
                    if (data.errors && data.errors.amount) {
                        msg = Array.isArray(data.errors.amount) ? data.errors.amount[0] : data.errors.amount;
                    }
                    throw new Error(msg);
                }
                return data;
            })
            .then((data) => {
                if (!data.success) {
                    openWalletFeedbackModal(
                        'Could not load wallet',
                        data.error || 'Failed to update wallet.',
                        false
                    );
                    return;
                }
                let body = 'Successfully loaded ₱' + amount.toFixed(2) + ' to ' + student.name + '\'s wallet.';
                if (data.inquiries_closed > 0) {
                    body += ' Deposit inquiry closed automatically.';
                }
                openWalletFeedbackModal('Wallet updated', body, true);
            })
            .catch((error) => {
                console.error(error);
                openWalletFeedbackModal(
                    'Something went wrong',
                    error.message || 'Could not update wallet. Stay logged in as staff and try again.',
                    false
                );
            });
        }

        document.getElementById('search-input')?.addEventListener('input', function(e) {
            const q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('tbody tr.student-row[data-search]').forEach(function(row) {
                const hay = row.getAttribute('data-search') || '';
                row.style.display = !q || hay.includes(q) ? '' : 'none';
            });
        });
    </script>

</x-layouts.staff-subpage>
