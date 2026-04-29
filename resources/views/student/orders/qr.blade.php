<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment QR {{ $order->order_number ?? 'ORD-'.$order->id }} — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="sticky top-0 z-10 border-b border-gray-200 bg-white shadow-sm">
        <div class="coinmeal-container flex flex-wrap items-center justify-between gap-2 py-3">
            <a href="{{ route('student.orders') }}"
                class="inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to orders
            </a>
        </div>
    </div>

    <div class="coinmeal-container py-6">
        <div class="mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="text-center">
                <p class="text-lg font-bold text-indigo-700">CoinMeal</p>
                <p class="text-xs text-gray-500">Scan to confirm order payment</p>
            </div>

            <div class="mt-5 space-y-1 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Order no.</span>
                    <span class="font-semibold text-gray-900">{{ $order->order_number ?? 'ORD-'.$order->id }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Canteen</span>
                    <span class="text-right font-medium text-gray-900">{{ $canteenLabel }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Customer</span>
                    <span class="text-right text-gray-900">{{ $studentName }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Amount</span>
                    <span class="font-semibold text-green-700">₱{{ number_format((float) ($order->payable_total ?? $order->total), 2) }}</span>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-center">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($qrToken) }}"
                    alt="Payment QR code"
                    class="mx-auto h-[240px] w-[240px] rounded-xl bg-white p-2 shadow-sm"
                >
                <p class="mt-3 text-xs text-indigo-900/80">Show this QR to canteen staff scanner.</p>
                <p class="mt-2 break-all rounded bg-white px-2 py-1 text-[11px] text-gray-600">{{ $qrToken }}</p>
            </div>
        </div>
    </div>
</body>

</html>
