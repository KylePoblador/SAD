<x-layouts.student title="Wallet QR" active="wallet">
    @php
        $purpose = $qrPurpose ?? 'payment';
        $isWalletLoad = $purpose === 'wallet_load';
    @endphp
    <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h1 class="text-lg font-bold text-gray-900">{{ $isWalletLoad ? 'Show this load QR to canteen staff' : 'Show this code to cashier' }}</h1>
        <p class="text-sm text-gray-600">Token: <span class="font-mono font-semibold">{{ $qrSession->token }}</span></p>
        <p class="text-sm text-gray-600">Amount: <span class="font-semibold">₱{{ number_format($qrSession->amount, 2) }}</span></p>
        <p class="text-sm text-gray-600">Expires: {{ $qrSession->expires_at->format('M d, Y h:i A') }}</p>
        @if ($isWalletLoad && !empty($walletLoadNote))
            <p class="text-sm text-gray-600">Note: <span class="font-medium">{{ $walletLoadNote }}</span></p>
        @endif
        <div class="rounded-xl border border-dashed border-green-300 bg-green-50 p-4 text-center">
            <p class="text-xs text-green-800">{{ $isWalletLoad ? 'Scan this QR at canteen wallet scanner' : 'Scan this QR at cashier' }}</p>
            <div id="wallet-qr-code" class="mx-auto mt-3 flex w-fit justify-center rounded-lg bg-white p-3"></div>
            <p class="mt-3 text-[11px] text-green-800/80">If camera scan is unavailable, staff can enter the token manually.</p>
        </div>
        <button type="button" id="btn-download-qr"
            class="inline-flex rounded-xl border border-green-200 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">
            Download QR image
        </button>
        <a href="{{ route('student.wallet') }}"
            class="inline-flex rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
            Back to wallet
        </a>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            (function() {
                var box = document.getElementById('wallet-qr-code');
                if (!box || typeof QRCode === 'undefined') return;
                var payload = @json($qrSession->token);
                // Keep payload simple (token only) so scanner flow is robust.
                new QRCode(box, {
                    text: payload,
                    width: 220,
                    height: 220,
                    correctLevel: QRCode.CorrectLevel.M
                });

                document.getElementById('btn-download-qr')?.addEventListener('click', function() {
                    var canvas = box.querySelector('canvas');
                    if (!canvas) return;
                    var link = document.createElement('a');
                    link.href = canvas.toDataURL('image/png');
                    link.download = payload + '.png';
                    link.click();
                });
            })();
        </script>
    @endpush
</x-layouts.student>
