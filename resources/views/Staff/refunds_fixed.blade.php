<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Refund Management — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body
    class="min-h-screen bg-gradient-to-b from-emerald-50/90 via-white to-sky-50/40 pb-24 font-sans text-gray-900 antialiased">

    @include('partials.coinmeal-dialogs')

    <header class="sticky top-0 z-10 border-b border-emerald-100/80 bg-white/90 shadow-sm backdrop-blur-sm">
        <div class="coinmeal-container flex items-center gap-3 py-3">
            <a href="{{ url('/staff/dashboard') }}"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition hover:bg-emerald-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1
                    class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-lg font-bold text-transparent">
                    Refund Management
                </h1>
                <p class="text-xs font-medium text-gray-500">Issue and track refunds</p>
            </div>
        </div>
    </header>

    <div class="coinmeal-container space-y-4 py-4 sm:space-y-5">

        <!-- Issue Refund Form -->
        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Issue Refund</h2>

            <form id="refund-form" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <input type="text" id="student-search" placeholder="Type student name..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <div id="student-suggestions"
                        class="mt-2 hidden max-h-64 overflow-y-auto space-y-1 border border-gray-200 rounded-lg bg-white p-2">
                    </div>
                    <input type="hidden" id="student_user_id" name="student_user_id">
                    <div id="selected-student" class="mt-2 text-sm text-green-600 font-medium"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Canteen</label>
                    <select name="canteen_id" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select canteen wallet to credit</option>
                        @foreach (config('canteens', []) as $slug => $canteen)
                            <option value="{{ $slug }}">{{ $canteen['label'] ?? strtoupper($slug) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount (₱)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <textarea name="reason" rows="3"
                        placeholder="Why is this refund being issued? (e.g., Double charged, Wrong amount, Cancelled order)"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        required></textarea>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 font-semibold text-white transition hover:shadow-lg disabled:opacity-50">
                    Issue Refund
                </button>
            </form>
        </div>

        <!-- Pending cancellation refunds -->
        <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
            <div class="mb-1 flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-gray-800">Pending review</h2>
                <span id="pending-count"
                    class="hidden rounded-full bg-amber-500 px-2.5 py-0.5 text-xs font-bold text-white">0</span>
            </div>
            <p class="mb-4 text-xs text-gray-600">Student-cancelled orders awaiting Refunded or Rejected.</p>
            <div id="pending-refund-list" class="space-y-3">
                <p class="text-sm text-gray-500">Loading…</p>
            </div>
        </div>

        <!-- Refund History -->
        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">All refunds</h2>

            <div id="refund-list" class="space-y-2">
                <p class="text-sm text-gray-500">Loading refund history...</p>
            </div>
        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const processUrlTemplate = @json(url('/staff/refunds/__ID__/process'));

        function statusBadge(status) {
            if (status === 'pending') return '<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pending</span>';
            if (status === 'rejected') return '<span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Rejected</span>';
            return '<span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Refunded</span>';
        }

        function formatDate(iso) {
            if (!iso) return '—';
            const d = new Date(iso);
            return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
        }

        async function processRefund(refundId, decision) {
            let staffNotes = null;
            if (decision === 'rejected') {
                staffNotes = prompt('Reason for rejecting this refund (required):');
                if (!staffNotes || !staffNotes.trim()) return;
            } else if (!confirm('Approve this refund and credit the student wallet?')) {
                return;
            }
            try {
                const response = await fetch(processUrlTemplate.replace('__ID__', refundId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ decision, staff_notes: staffNotes }),
                });
                const data = await response.json();
                if (response.ok) {
                    await CoinmealDialog.alert({ title: decision === 'refunded' ? 'Refund approved' : 'Refund rejected', message: data.message, variant: 'success', okLabel: 'OK' });
                    loadRefundHistory();
                } else {
                    await CoinmealDialog.alert({ title: 'Could not process', message: data.message || 'Try again.', variant: 'error', okLabel: 'OK' });
                }
            } catch (e) { console.error(e); }
        }

        async function loadRefundHistory() {
            try {
                const response = await fetch('{{ route('staff.refunds.history') }}');
                const data = await response.json();

                const pendingList = document.getElementById('pending-refund-list');
                const refundList = document.getElementById('refund-list');
                const pendingBadge = document.getElementById('pending-count');
                const pending = (data.refunds || []).filter(r => r.status === 'pending');
                const processed = (data.refunds || []).filter(r => r.status !== 'pending');

                if (pendingBadge) {
                    if (pending.length > 0) {
                        pendingBadge.textContent = String(pending.length);
                        pendingBadge.classList.remove('hidden');
                    } else {
                        pendingBadge.classList.add('hidden');
                    }
                }

                pendingList.innerHTML = pending.length === 0
                    ? '<p class="text-sm text-gray-500">No cancelled orders awaiting review.</p>'
                    : pending.map(refund => `
                        <div class="rounded-lg border border-amber-200 bg-white p-4 shadow-sm">
                            <p class="font-semibold text-gray-900">${refund.student.name}</p>
                            <p class="text-sm text-gray-600">₱${parseFloat(refund.amount).toFixed(2)} · ${refund.order?.order_number || 'Order'}</p>
                            <p class="mt-1 text-xs text-gray-500">${refund.reason}</p>
                            <p class="mt-1 text-xs text-gray-400">Requested ${formatDate(refund.created_at)}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" onclick="processRefund(${refund.id}, 'refunded')" class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700">Refunded</button>
                                <button type="button" onclick="processRefund(${refund.id}, 'rejected')" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">Rejected</button>
                            </div>
                        </div>
                    `).join('');

                if (processed.length === 0) {
                    refundList.innerHTML = '<p class="text-sm text-gray-500">No processed refunds yet.</p>';
                    return;
                }

                refundList.innerHTML = processed.map(refund => `
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">${refund.student.name}</p>
                                <p class="text-sm text-gray-600">₱${parseFloat(refund.amount).toFixed(2)}</p>
                                <p class="mt-1 text-xs text-gray-500">${refund.reason}</p>
                                ${refund.order?.order_number ? `<p class="mt-0.5 text-xs text-indigo-600">${refund.order.order_number}</p>` : ''}
                                ${refund.staff_notes ? `<p class="mt-1 text-xs text-red-600">${refund.staff_notes}</p>` : ''}
                                <p class="mt-1 text-xs text-gray-400">${formatDate(refund.refunded_at || refund.created_at)}</p>
                            </div>
                            <div class="text-right shrink-0">${statusBadge(refund.status)}</div>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading refund history:', error);
                document.getElementById('refund-list').innerHTML = '<p class="text-sm text-red-500">Error loading refund history</p>';
                document.getElementById('pending-refund-list').innerHTML = '<p class="text-sm text-red-500">Error loading pending refunds</p>';
            }
        }

        // Get all students
        async function getAllStudents() {
            try {
                const response = await fetch('/api/students');
                if (!response.ok) throw new Error('Failed to fetch students');
                return await response.json();
            } catch (error) {
                console.error('Error fetching students:', error);
                return [];
            }
        }

        let allStudents = [];

        // Student search with local filtering
        document.getElementById('student-search').addEventListener('input', async (e) => {
            const query = e.target.value.toLowerCase().trim();
            const suggestions = document.getElementById('student-suggestions');

            if (query.length === 0) {
                suggestions.classList.add('hidden');
                return;
            }

            // If first time, load all students
            if (allStudents.length === 0) {
                allStudents = await getAllStudents();
            }

            // Filter locally
            const filtered = allStudents.filter(student =>
                student.name.toLowerCase().includes(query) ||
                (student.student_id && student.student_id.toLowerCase().includes(query))
            ).slice(0, 10);

            if (filtered.length > 0) {
                suggestions.innerHTML = filtered.map(student => `
                    <button type="button"
                        class="w-full text-left rounded-lg border border-gray-200 p-2 hover:bg-emerald-50 transition"
                        onclick="selectStudent(${student.id}, '${student.name.replace(/'/g, "\\'")}')">
                        <p class="font-medium text-gray-900">${student.name}</p>
                        <p class="text-xs text-gray-500">${student.student_id || 'No ID'} | ${student.email || ''}</p>
                    </button>
                `).join('');
                suggestions.classList.remove('hidden');
            } else {
                suggestions.innerHTML = '<p class="text-sm text-gray-500 p-2">No students found</p>';
                suggestions.classList.remove('hidden');
            }
        });

        function selectStudent(id, name) {
            document.getElementById('student_user_id').value = id;
            document.getElementById('student-search').value = '';
            document.getElementById('selected-student').textContent = `✅ Selected: ${name}`;
            document.getElementById('student-suggestions').classList.add('hidden');
        }

        // Submit refund form
        document.getElementById('refund-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const studentId = document.getElementById('student_user_id').value;
            if (!studentId) {
                await CoinmealDialog.alert({
                    title: 'Select a student',
                    message: 'Search and choose a student before issuing a refund.',
                    variant: 'info',
                    okLabel: 'OK',
                });
                return;
            }

            const canteenId = document.querySelector('[name="canteen_id"]')?.value;
            if (!canteenId) {
                await CoinmealDialog.alert({
                    title: 'Select a canteen',
                    message: 'Choose which canteen wallet should receive the refund.',
                    variant: 'info',
                    okLabel: 'OK',
                });
                return;
            }

            const formData = {
                student_user_id: parseInt(studentId),
                canteen_id: canteenId,
                amount: parseFloat(document.querySelector('[name="amount"]').value),
                reason: document.querySelector('[name="reason"]').value,
            };

            try {
                const response = await fetch('{{ route('staff.refunds.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();

                if (response.ok) {
                    await CoinmealDialog.alert({
                        title: 'Refund processed',
                        message: `Student’s new wallet balance: ₱${Number(data.student_new_balance ?? 0).toFixed(2)}`,
                        variant: 'success',
                        okLabel: 'Done',
                    });
                    document.getElementById('refund-form').reset();
                    document.getElementById('selected-student').textContent = '';
                    loadRefundHistory();
                } else {
                    await CoinmealDialog.alert({
                        title: 'Refund failed',
                        message: data.message || 'The refund could not be completed.',
                        variant: 'error',
                        okLabel: 'OK',
                    });
                }
            } catch (error) {
                console.error('Error submitting refund:', error);
                await CoinmealDialog.alert({
                    title: 'Error',
                    message: 'Something went wrong while processing the refund. Please try again.',
                    variant: 'error',
                    okLabel: 'OK',
                });
            }
        });

        // Initial load
        loadRefundHistory();
        setInterval(loadRefundHistory, 30000); // Refresh every 30 seconds

        // Pre-load students on page load
        getAllStudents().then(students => {
            allStudents = students;
        });
    </script>

</body>

</html>
