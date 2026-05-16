<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coin Transfer Receipt — CoinMeal</title>
    @include('partials.coinmeal-assets')
    <style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <div class="no-print sticky top-0 z-10 border-b border-gray-200 bg-white shadow-sm">
        <div class="coinmeal-container flex flex-wrap items-center justify-between gap-2 py-3">
            <a href="{{ route('student.transactions') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to transactions
            </a>
            <button type="button" onclick="window.print()"
                class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-700">
                Print / Save as PDF
            </button>
        </div>
    </div>

    <div class="coinmeal-container py-6 print:py-4">
        <div class="mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm print:border-0 print:shadow-none">
            <div class="text-center">
                <p class="text-lg font-bold text-violet-700">CoinMeal</p>
                <p class="text-xs text-gray-500">University of Southern Mindanao</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-400">Coin Transfer Receipt</p>
            </div>

            <div class="mt-6 space-y-1 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Reference</span>
                    <span class="font-semibold text-gray-900">CT-{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Date</span>
                    <span class="text-gray-900">{{ $transfer->created_at->format('M d, Y · g:i A') }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Canteen</span>
                    <span class="text-right font-medium text-gray-900">{{ $canteenLabel }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">From</span>
                    <span class="text-right text-gray-900">{{ $transfer->sender?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">To</span>
                    <span class="text-right text-gray-900">{{ $transfer->receiver?->name ?? '—' }}</span>
                </div>
                @if($transfer->note)
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Note</span>
                    <span class="text-right text-gray-900">{{ $transfer->note }}</span>
                </div>
                @endif
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Direction</span>
                    <span class="font-medium {{ $isSender ? 'text-red-600' : 'text-green-600' }}">
                        {{ $isSender ? '↑ Sent' : '↓ Received' }}
                    </span>
                </div>
            </div>

            <hr class="my-4 border-gray-100">

            <div class="flex items-center justify-between">
                <span class="text-base font-bold text-gray-800">Amount</span>
                <span class="text-xl font-bold {{ $isSender ? 'text-red-500' : 'text-green-600' }}">
                    {{ $isSender ? '-' : '+' }}₱{{ number_format((float) $transfer->amount, 2) }}
                </span>
            </div>

            <p class="mt-6 text-center text-[11px] leading-relaxed text-gray-400">
                Coins were transferred between CoinMeal accounts at <strong>{{ $canteenLabel }}</strong>.
            </p>
            <p class="mt-2 text-center text-[11px] leading-relaxed text-gray-400">
                Generated from your CoinMeal account. For questions, contact your canteen's staff.
            </p>
        </div>
    </div>
</body>
</html>
