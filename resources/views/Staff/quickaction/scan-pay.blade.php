<x-layouts.staff-subpage title="QR scan wallet load" :subtitle="$canteenName">
    @if (session('status') === 'qr-payment-processed')
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
            QR payment processed successfully.
        </div>
    @endif
    @if (session('status') === 'wallet-load-preview-ready')
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            Wallet load QR scanned. Please review and confirm below.
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            {{ $errors->first() }}
        </div>
    @endif
    @if (!empty($walletLoadPreview))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <h3 class="text-base font-bold text-emerald-900">Confirm wallet load</h3>
            <p class="mt-1 text-sm text-emerald-900">Student: <strong>{{ $walletLoadPreview['student_name'] }}</strong>
                @if (!empty($walletLoadPreview['student_id']))
                    ({{ $walletLoadPreview['student_id'] }})
                @endif
            </p>
            <p class="text-sm text-emerald-900">Amount: <strong>₱{{ number_format((float) $walletLoadPreview['amount'], 2) }}</strong></p>
            <p class="text-xs text-emerald-800">Token: {{ $walletLoadPreview['token'] }}</p>
            <form method="POST" action="{{ route('staff.scan-pay.confirm-wallet-load') }}" class="mt-3">
                @csrf
                <input type="hidden" name="qr_session_id" value="{{ $walletLoadPreview['qr_session_id'] }}">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Confirm and load wallet
                </button>
            </form>
        </div>
    @endif
    <div class="mx-auto w-full max-w-4xl space-y-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 border-b border-gray-100 pb-4">
                <h2 class="text-xl font-bold text-gray-900">QR Scan Wallet Load</h2>
                <p class="mt-1 text-sm text-gray-600">Point the camera at the student's wallet-load QR to fetch name and amount.</p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4 sm:p-5">
                <div class="mx-auto w-full max-w-md">
                <div id="reader" class="aspect-square w-full overflow-hidden rounded-xl border-2 border-dashed border-emerald-300 bg-black/5"></div>
                </div>
                <p id="scan-status" class="mt-3 text-center text-sm text-gray-700">Ready to start QR camera scanning.</p>
                <div class="mt-3 flex justify-center">
                    <button type="button" id="btn-start-scan"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Start QR Scanner
                    </button>
                </div>
            </div>
        </div>

        <details class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <summary class="cursor-pointer text-sm font-semibold text-amber-900">Manual token fallback</summary>
            <p class="mt-2 text-xs text-amber-800">Use this only if camera scan is unavailable.</p>
            <form id="scan-pay-form" method="POST" action="{{ route('staff.scan-pay.process') }}" class="mt-3 space-y-3">
                @csrf
                <input type="text" id="token-input" name="token" required placeholder="CM-XXXXXXXXXX"
                    class="w-full rounded-xl border border-amber-300 px-3 py-2.5 text-sm">
                <button type="submit"
                    class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                    Process payment manually
                </button>
            </form>
        </details>
    </div>

    @push('scripts')
        <style>
            #reader {
                position: relative;
            }

            #reader video {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover;
                border-radius: 0.75rem;
            }

            #reader__dashboard_section_csr,
            #reader__dashboard_section_swaplink {
                display: none !important;
            }
        </style>
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script>
            (function() {
                var startBtn = document.getElementById('btn-start-scan');
                var tokenInput = document.getElementById('token-input');
                var statusEl = document.getElementById('scan-status');
                var form = document.getElementById('scan-pay-form');
                var scanner = null;
                var isRunning = false;

                function setStatus(message, isError) {
                    if (!statusEl) return;
                    statusEl.textContent = message;
                    statusEl.classList.toggle('text-red-600', !!isError);
                    statusEl.classList.toggle('text-gray-600', !isError);
                }

                function normalizeToken(value) {
                    return String(value || '').trim();
                }

                async function getPreferredCamera() {
                    if (typeof Html5Qrcode === 'undefined' || typeof Html5Qrcode.getCameras !== 'function') {
                        return {
                            facingMode: {
                                ideal: 'environment'
                            }
                        };
                    }

                    try {
                        var devices = await Html5Qrcode.getCameras();
                        if (!devices || !devices.length) {
                            return {
                                facingMode: {
                                    ideal: 'environment'
                                }
                            };
                        }

                        var backCamera = devices.find(function(device) {
                            return /back|rear|environment/i.test(device.label || '');
                        });

                        if (backCamera) {
                            return backCamera.id;
                        }

                        return devices[0].id;
                    } catch (e) {
                        return {
                            facingMode: {
                                ideal: 'environment'
                            }
                        };
                    }
                }

                async function startScanner() {
                    if (isRunning) return;
                    if (typeof Html5Qrcode === 'undefined') {
                        setStatus('Scanner library failed to load. Use manual token input.', true);
                        return;
                    }
                    scanner = new Html5Qrcode('reader');
                    try {
                        var cameraConfig = await getPreferredCamera();
                        var scannerConfig = {
                            fps: 10,
                            qrbox: function(viewportWidth, viewportHeight) {
                                var minEdge = Math.min(viewportWidth, viewportHeight);
                                var size = Math.floor(minEdge * 0.65);
                                return {
                                    width: size,
                                    height: size
                                };
                            }
                        };
                        if (typeof Html5QrcodeSupportedFormats !== 'undefined' && Html5QrcodeSupportedFormats.QR_CODE) {
                            scannerConfig.formatsToSupport = [Html5QrcodeSupportedFormats.QR_CODE];
                        }

                        await scanner.start(cameraConfig, scannerConfig, async function(decodedText) {
                            var token = normalizeToken(decodedText);
                            if (!token) return;
                            tokenInput.value = token;
                            setStatus('QR detected. Processing payment...', false);
                            if (scanner && isRunning) {
                                await scanner.stop();
                                isRunning = false;
                            }
                            form.submit();
                        }, function() {});
                        isRunning = true;
                        setStatus('Scanner is active (back camera preferred). Point camera at student QR code.', false);
                    } catch (e) {
                        setStatus('Unable to access camera. Use manual token input.', true);
                    }
                }

                startBtn?.addEventListener('click', startScanner);
            })();
        </script>
    @endpush
</x-layouts.staff-subpage>
