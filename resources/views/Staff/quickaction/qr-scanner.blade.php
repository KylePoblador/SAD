<x-layouts.staff-subpage title="QR scanner" subtitle="Scan wallet load QR">
    @if (session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        {{-- Narrow centered camera preview; qrbox JS matches this box width (minus padding). --}}
        <div class="mb-3 flex justify-center">
            <div id="reader"
                class="relative aspect-square w-full max-w-[272px] overflow-hidden rounded-lg bg-black/[0.06] shadow-inner"></div>
        </div>
        <p id="qr-help" class="mb-2 text-xs text-gray-500">
            Scanner is loading...
        </p>
        <input id="qr-file-input" type="file" accept="image/*" class="hidden">
        <button id="btn-upload-qr" type="button"
            class="mb-3 w-full rounded-lg border border-gray-200 bg-white py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Upload QR image (screenshot / gallery)
        </button>
        <label for="qr-token" class="mb-2 block text-xs font-semibold text-gray-600">Or paste token manually</label>
        <input id="qr-token" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="QR token">
        <button id="btn-open-token" type="button" class="mt-3 w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white">
            Open confirmation
        </button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function () {
            const helpEl = document.getElementById('qr-help');
            const readerId = 'reader';
            let cameraScanner = null;

            function setHelp(msg, isError) {
                if (!helpEl) return;
                helpEl.textContent = msg;
                helpEl.className = isError
                    ? 'mb-2 text-xs text-red-600'
                    : 'mb-2 text-xs text-gray-500';
            }

            /** Browser treats plain HTTP on LAN / *.test as non-secure — camera API is blocked there. */
            function cameraAllowedHere() {
                if (window.isSecureContext) return true;
                const h = location.hostname;
                return h === 'localhost' || h === '127.0.0.1' || h === '[::1]';
            }

            function normalizeDecodedPayload(raw) {
                let t = String(raw || '').trim();
                const urlMatch = t.match(/\/staff\/wallet-load\/([^/?#\s]+)/i);
                if (urlMatch) {
                    try {
                        return decodeURIComponent(urlMatch[1]);
                    } catch (e) {
                        return urlMatch[1];
                    }
                }
                return t;
            }

            function applyDecoded(decodedText) {
                const raw = String(decodedText || '').trim();
                const input = document.getElementById('qr-token');
                if (input) input.value = raw;
                setHelp('QR detected. Tap "Open confirmation".', false);
            }

            function openToken() {
                const raw = String(document.getElementById('qr-token')?.value || '').trim();
                if (!raw) return;

                if (/^https?:\/\//i.test(raw)) {
                    window.location.href = raw;
                    return;
                }

                const walletRel = raw.match(/^\/?staff\/wallet-load\/([^/?#\s]+)\/?$/i);
                if (walletRel) {
                    window.location.href =
                        @json(url('/staff/wallet-load')) + '/' + encodeURIComponent(walletRel[1]);
                    return;
                }

                const payload = normalizeDecodedPayload(raw);
                if (!payload) return;
                window.location.href = @json(url('/staff/wallet-load')) + '/' + encodeURIComponent(payload);
            }

            async function stopCameraScanner() {
                if (!cameraScanner) return;
                try {
                    await cameraScanner.stop();
                } catch (e) {}
                try {
                    cameraScanner.clear();
                } catch (e) {}
                cameraScanner = null;
            }

            function scannerConfig() {
                return {
                    fps: 15,
                    qrbox: function (viewfinderWidth, viewfinderHeight) {
                        const pad = 12;
                        const width = Math.floor(Math.max(100, viewfinderWidth - pad * 2));
                        const height = Math.floor(Math.max(100, viewfinderHeight - pad * 2));
                        return { width: width, height: height };
                    },
                    aspectRatio: 1,
                };
            }

            async function startCameraScanner() {
                if (!cameraAllowedHere()) return;

                await stopCameraScanner();

                const cfg = scannerConfig();

                const attempts = [{ facingMode: 'environment' }, { facingMode: 'user' }];
                let lastErr = null;

                for (const cam of attempts) {
                    const scanner = new Html5Qrcode(readerId);
                    try {
                        await scanner.start(cam, cfg, function (decodedText) {
                            applyDecoded(decodedText);
                            scanner.stop().catch(function () {});
                            cameraScanner = null;
                        }, function () {});
                        cameraScanner = scanner;
                        setHelp('Point the camera at the student\'s wallet load QR.', false);
                        return;
                    } catch (e) {
                        lastErr = e;
                        try {
                            await scanner.stop();
                        } catch (err) {}
                        try {
                            scanner.clear();
                        } catch (err) {}
                    }
                }

                try {
                    const cameras = await Html5Qrcode.getCameras();
                    if (cameras && cameras.length) {
                        const s2 = new Html5Qrcode(readerId);
                        await s2.start(cameras[0].id, cfg, function (decodedText) {
                            applyDecoded(decodedText);
                            s2.stop().catch(function () {});
                            cameraScanner = null;
                        }, function () {});
                        cameraScanner = s2;
                        setHelp('Point the camera at the student\'s wallet load QR.', false);
                        return;
                    }
                } catch (e) {
                    lastErr = e;
                }

                throw lastErr || new Error('No usable camera');
            }

            async function decodeQrFromFile(file) {
                await stopCameraScanner();

                const q = new Html5Qrcode(readerId);
                try {
                    const decodedText = await q.scanFile(file, false);
                    applyDecoded(decodedText);
                } finally {
                    try {
                        await q.clear();
                    } catch (e) {}
                }

                if (cameraAllowedHere()) {
                    try {
                        await startCameraScanner();
                    } catch (e) {
                        setHelp('QR read from image. Camera did not restart — use upload again or paste token.', false);
                    }
                }
            }

            document.getElementById('btn-open-token')?.addEventListener('click', openToken);
            document.getElementById('btn-upload-qr')?.addEventListener('click', function () {
                document.getElementById('qr-file-input')?.click();
            });
            document.getElementById('qr-file-input')?.addEventListener('change', function (ev) {
                const file = ev.target.files && ev.target.files[0];
                ev.target.value = '';
                if (!file || !window.Html5Qrcode) return;
                decodeQrFromFile(file).catch(function (err) {
                    setHelp(
                        'Could not read QR from this image (' + (err && err.message ? err.message : 'try another photo') + '). Paste the token if needed.',
                        true
                    );
                    if (cameraAllowedHere()) {
                        startCameraScanner().catch(function () {});
                    }
                });
            });

            window.addEventListener('load', function () {
                if (!window.Html5Qrcode) {
                    setHelp('QR scanner library failed to load. Check internet and retry.', true);
                    return;
                }

                if (!cameraAllowedHere()) {
                    const readerEl = document.getElementById(readerId);
                    if (readerEl) {
                        readerEl.innerHTML =
                            '<div class="flex aspect-square min-h-[180px] flex-col items-center justify-center gap-2 px-3 py-4 text-center text-xs leading-snug text-gray-600">' +
                            '<span class="font-semibold text-gray-800">Camera blocked on plain HTTP</span>' +
                            '<span>Browsers only allow camera on HTTPS or <code class="rounded bg-gray-100 px-1">localhost</code>. Use <strong>Upload QR image</strong> with a screenshot of the wallet load QR, or paste the token.</span>' +
                            '</div>';
                    }
                    setHelp(
                        'Upload a screenshot of the wallet load QR, or paste the token — live camera needs HTTPS or localhost.',
                        true
                    );
                    return;
                }

                startCameraScanner().catch(function (err) {
                    setHelp(
                        'Unable to start camera (' +
                            (err && err.message ? err.message : 'no camera permission') +
                            '). Try "Upload QR image" or paste the token.',
                        true
                    );
                });
            });
        })();
    </script>
</x-layouts.staff-subpage>
