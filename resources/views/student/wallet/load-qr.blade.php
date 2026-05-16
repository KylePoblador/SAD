<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet load QR — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="sticky top-0 z-10 border-b border-gray-200 bg-white shadow-sm">
        <div class="coinmeal-container flex flex-wrap items-center justify-between gap-2 py-3">
            <a href="{{ route('student.wallet') }}"
                class="inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to wallet
            </a>
        </div>
    </div>

    <div class="coinmeal-container py-6">
        <div class="mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="text-center">
                <p class="text-lg font-bold text-amber-700">CoinMeal</p>
                <p class="text-xs text-gray-500">Wallet top-up · staff scans to confirm cash paid</p>
            </div>

            <div class="mt-5 space-y-1 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Canteen</span>
                    <span class="text-right font-medium text-gray-900">{{ $canteenLabel }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Student</span>
                    <span class="text-right text-gray-900">{{ $studentName }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Amount</span>
                    <span class="font-semibold text-green-700">₱{{ number_format((float) $entry->amount, 2) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Expires</span>
                    <span class="text-right text-xs text-gray-600">{{ $entry->expires_at->format('M d, Y g:i A') }}</span>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($staffScanUrl) }}"
                    alt="Wallet load QR code"
                    class="mx-auto h-[240px] w-[240px] rounded-xl bg-white p-2 shadow-sm">
                <p class="mt-3 text-xs text-amber-950/85">Pay cash at the counter, then show this QR so staff can scan and credit your wallet.</p>
                <p class="mt-2 text-[11px] text-amber-900/70">Works with the staff <strong>QR scanner</strong> app screen.</p>
            </div>
        </div>
    </div>
</body>

</html>
