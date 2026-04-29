<x-layouts.staff-subpage title="QR scanner" subtitle="Scan and verify order payment">
    @if (session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div id="reader" class="mb-3"></div>
        <p id="qr-help" class="mb-2 text-xs text-gray-500">
            Camera scanner is loading...
        </p>
        <label for="qr-token" class="mb-2 block text-xs font-semibold text-gray-600">Paste scanned token</label>
        <input id="qr-token" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="QR token">
        <button id="btn-open-token" type="button" class="mt-3 w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white">
            Open confirmation
        </button>
    </div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        (function () {
            const helpEl = document.getElementById('qr-help');

            function setHelp(msg, isError) {
                if (!helpEl) return;
                helpEl.textContent = msg;
                helpEl.className = isError
                    ? 'mb-2 text-xs text-red-600'
                    : 'mb-2 text-xs text-gray-500';
            }

            function openToken() {
                const token = document.getElementById('qr-token')?.value?.trim();
                if (!token) return;
                window.location.href = @json(url('/staff/qr')) + '/' + encodeURIComponent(token);
            }
            document.getElementById('btn-open-token')?.addEventListener('click', openToken);

            window.addEventListener('load', function () {
                const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
                if (!isSecure) {
                    setHelp('Camera scan needs HTTPS or localhost. Use manual token input below for now.', true);
                    return;
                }
                if (!window.Html5Qrcode) {
                    setHelp('QR scanner library failed to load. Check internet and retry.', true);
                    return;
                }
                const scanner = new Html5Qrcode('reader');
                scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 220 },
                    function (decodedText) {
                        document.getElementById('qr-token').value = decodedText;
                        setHelp('QR detected. Tap "Open confirmation".', false);
                        scanner.stop().catch(function () {});
                    },
                    function () {}
                ).then(function () {
                    setHelp('Point the camera to the QR code.', false);
                }).catch(function (err) {
                    setHelp('Unable to start camera scanner (' + (err?.message || 'unknown error') + '). Use token input below.', true);
                });
            });
        })();
    </script>
</x-layouts.staff-subpage>
