<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Refund History — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body
    class="min-h-screen bg-gradient-to-b from-emerald-50/90 via-white to-sky-50/40 pb-24 font-sans text-gray-900 antialiased">

    <header class="sticky top-0 z-10 border-b border-emerald-100/80 bg-white/90 shadow-sm backdrop-blur-sm">
        <div class="coinmeal-container flex items-start justify-between gap-3 py-3">
            <div class="min-w-0">
                <h1
                    class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-lg font-bold text-transparent">
                    My Refunds
                </h1>
                <p class="text-xs font-medium text-gray-500">Refund history</p>
            </div>
            <a href="{{ route('student.wallet') }}"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </header>

    <div class="coinmeal-container space-y-4 py-4 sm:space-y-5">

        <!-- Refund Summary -->
        <div class="rounded-xl border border-green-100 bg-gradient-to-r from-green-50 to-emerald-50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Refunded</p>
                    <p class="mt-1 text-3xl font-bold text-green-600">₱<span id="total-refunds">0.00</span></p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-600">Refunds Received</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600"><span id="refund-count">0</span></p>
                </div>
            </div>
        </div>

        <!-- Refund List -->
        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Refund Transactions</h2>

            <div id="refund-list" class="space-y-3">
                <div class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No refunds received yet</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        async function loadRefundHistory() {
            try {
                const response = await fetch(`{{ route('student.refunds.history', auth()->id()) }}`);
                const data = await response.json();

                // Update summary
                document.getElementById('total-refunds').textContent = parseFloat(data.total_refunds).toFixed(2);
                document.getElementById('refund-count').textContent = data.refund_count;

                const refundList = document.getElementById('refund-list');

                if (data.refunds.length === 0) {
                    refundList.innerHTML = `
                        <div class="flex items-center justify-center py-8">
                            <div class="text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No refunds received yet</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                refundList.innerHTML = data.refunds.map(refund => `
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-gradient-to-r from-green-50/50 to-emerald-50/50 transition hover:shadow-md">
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Refund from ${refund.staff.name}</p>
                            <p class="text-xs text-gray-600">${refund.reason}</p>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-green-600">+₱${parseFloat(refund.amount).toFixed(2)}</p>
                    <p class="text-xs text-gray-500">
                        ${new Date(refund.refunded_at).toLocaleDateString()}
                        ${new Date(refund.refunded_at).toLocaleTimeString()}
                    </p>
                </div>
            </div>
        </div>
                `).join('');
            } catch (error) {
                console.error('Error loading refund history:', error);
                document.getElementById('refund-list').innerHTML =
                    '<p class="text-sm text-red-500">Error loading refund history</p>';
            }
        }

        // Initial load
        loadRefundHistory();
        // Refresh every minute
        setInterval(loadRefundHistory, 60000);
    </script>

</body>

</html>
