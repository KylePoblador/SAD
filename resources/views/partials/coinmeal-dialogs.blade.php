@once
    <div id="coinmeal-dialog-root" class="fixed inset-0 z-[90] hidden items-center justify-center p-4 sm:p-6"
        aria-hidden="true">
        <button type="button" id="coinmeal-dialog-backdrop"
            class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm transition-opacity"
            aria-label="Dismiss dialog"></button>

        <div role="dialog" aria-modal="true" aria-labelledby="coinmeal-dialog-title" id="coinmeal-dialog-panel"
            class="relative max-h-[min(90vh,560px)] w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200/90">
            <div id="coinmeal-dialog-accent"
                class="h-1 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-sky-500"></div>

            <div class="max-h-[min(85vh,520px)] overflow-y-auto px-5 pb-5 pt-4 sm:px-6 sm:pb-6 sm:pt-5">
                <div id="coinmeal-dialog-icon-wrap" class="mb-3 hidden">
                    <span id="coinmeal-dialog-icon"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl ring-8 ring-slate-50"></span>
                </div>

                <h2 id="coinmeal-dialog-title" class="text-lg font-bold tracking-tight text-slate-900"></h2>
                <p id="coinmeal-dialog-message"
                    class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600"></p>

                <div id="coinmeal-dialog-actions-confirm"
                    class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
                    <button type="button" id="coinmeal-dialog-cancel"
                        class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto">
                        Cancel
                    </button>
                    <button type="button" id="coinmeal-dialog-confirm"
                        class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition sm:w-auto">
                        Confirm
                    </button>
                </div>

                <div id="coinmeal-dialog-actions-alert"
                    class="mt-6 flex justify-end border-t border-slate-100 pt-4 sm:pt-5">
                    <button type="button" id="coinmeal-dialog-ok"
                        class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-emerald-700 hover:to-teal-700 sm:w-auto">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('coinmeal-dialog-root');
            const backdrop = document.getElementById('coinmeal-dialog-backdrop');
            const titleEl = document.getElementById('coinmeal-dialog-title');
            const messageEl = document.getElementById('coinmeal-dialog-message');
            const iconWrap = document.getElementById('coinmeal-dialog-icon-wrap');
            const iconSlot = document.getElementById('coinmeal-dialog-icon');
            const confirmActions = document.getElementById('coinmeal-dialog-actions-confirm');
            const alertActions = document.getElementById('coinmeal-dialog-actions-alert');
            const btnCancel = document.getElementById('coinmeal-dialog-cancel');
            const btnConfirm = document.getElementById('coinmeal-dialog-confirm');
            const btnOk = document.getElementById('coinmeal-dialog-ok');

            if (!root || !backdrop || !titleEl || !messageEl || !confirmActions || !alertActions || !btnCancel || !
                btnConfirm || !btnOk) {
                return;
            }

            let mode = null;
            let resolver = null;

            function iconSvg(pathD, strokeClass) {
                return '<svg class="h-6 w-6 ' + strokeClass +
                    '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' +
                    pathD + '" /></svg>';
            }

            function applyConfirmVariant(variant) {
                btnConfirm.className =
                    'inline-flex min-h-[44px] w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition sm:w-auto';
                iconWrap.classList.add('hidden');
                iconSlot.innerHTML = '';

                if (variant === 'danger') {
                    btnConfirm.classList.add('bg-rose-600', 'hover:bg-rose-700');
                    iconWrap.classList.remove('hidden');
                    iconSlot.className =
                        'inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-8 ring-rose-50/80';
                    iconSlot.innerHTML = iconSvg(
                        'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
                        ''
                    );
                    return;
                }
                if (variant === 'neutral') {
                    btnConfirm.classList.add('bg-slate-700', 'hover:bg-slate-800');
                    return;
                }
                btnConfirm.classList.add('bg-gradient-to-r', 'from-emerald-600', 'to-teal-600', 'hover:from-emerald-700',
                    'hover:to-teal-700');
                iconWrap.classList.remove('hidden');
                iconSlot.className =
                    'inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-8 ring-emerald-50/80';
                iconSlot.innerHTML = iconSvg(
                    'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', '');
            }

            function applyAlertVariant(variant) {
                btnOk.className =
                    'inline-flex min-h-[44px] w-full items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition sm:w-auto';
                iconWrap.classList.add('hidden');
                iconSlot.innerHTML = '';

                if (variant === 'success') {
                    btnOk.classList.add('bg-gradient-to-r', 'from-emerald-600', 'to-teal-600', 'hover:from-emerald-700',
                        'hover:to-teal-700');
                    iconWrap.classList.remove('hidden');
                    iconSlot.className =
                        'inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-8 ring-emerald-50/80';
                    iconSlot.innerHTML = iconSvg('M5 13l4 4L19 7', '');
                    return;
                }
                if (variant === 'error') {
                    btnOk.classList.add('bg-rose-600', 'hover:bg-rose-700');
                    iconWrap.classList.remove('hidden');
                    iconSlot.className =
                        'inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-8 ring-rose-50/80';
                    iconSlot.innerHTML = iconSvg('M6 18 18 6M6 6l12 12', '');
                    return;
                }
                btnOk.classList.add('bg-slate-800', 'hover:bg-slate-900');
                iconWrap.classList.remove('hidden');
                iconSlot.className =
                    'inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-8 ring-sky-50/80';
                iconSlot.innerHTML = iconSvg(
                    'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
                    '');
            }

            function hide() {
                root.classList.add('hidden');
                root.classList.remove('flex');
                root.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
                document.removeEventListener('keydown', onDocKey);
                resolver = null;
                mode = null;
            }

            function show() {
                root.classList.remove('hidden');
                root.classList.add('flex');
                root.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
                document.addEventListener('keydown', onDocKey);
            }

            function finishConfirm(ok) {
                const fn = resolver;
                hide();
                if (typeof fn === 'function') fn(ok);
            }

            function finishAlert() {
                const fn = resolver;
                hide();
                if (typeof fn === 'function') fn();
            }

            function onDocKey(ev) {
                if (ev.key === 'Escape') {
                    ev.preventDefault();
                    if (mode === 'confirm') finishConfirm(false);
                    else finishAlert();
                }
            }

            backdrop.addEventListener('click', function () {
                if (mode === 'confirm') finishConfirm(false);
                else finishAlert();
            });

            btnCancel.addEventListener('click', function () {
                finishConfirm(false);
            });
            btnConfirm.addEventListener('click', function () {
                finishConfirm(true);
            });
            btnOk.addEventListener('click', finishAlert);

            window.CoinmealDialog = {
                confirm: function (opts) {
                    opts = opts || {};
                    const title = opts.title || 'Please confirm';
                    const message = opts.message || '';
                    const variant = opts.variant || 'primary';
                    const confirmLabel = opts.confirmLabel || 'Confirm';
                    const cancelLabel = opts.cancelLabel || 'Cancel';

                    return new Promise(function (resolve) {
                        resolver = resolve;
                        mode = 'confirm';
                        titleEl.textContent = title;
                        messageEl.textContent = message;
                        btnCancel.textContent = cancelLabel;
                        btnConfirm.textContent = confirmLabel;
                        applyConfirmVariant(variant);
                        confirmActions.classList.remove('hidden');
                        alertActions.classList.add('hidden');
                        show();
                        btnConfirm.focus();
                    });
                },
                alert: function (opts) {
                    opts = opts || {};
                    const title = opts.title || '';
                    const message = opts.message || '';
                    const variant = opts.variant || 'info';
                    const okLabel = opts.okLabel || 'OK';

                    return new Promise(function (resolve) {
                        resolver = resolve;
                        mode = 'alert';
                        titleEl.textContent = title || (variant === 'error' ? 'Something went wrong' : 'Notice');
                        messageEl.textContent = message;
                        btnOk.textContent = okLabel;
                        applyAlertVariant(variant);
                        confirmActions.classList.add('hidden');
                        alertActions.classList.remove('hidden');
                        show();
                        btnOk.focus();
                    });
                },
            };

            function bindConfirmForms() {
                document.querySelectorAll('form[data-coinmeal-confirm]').forEach(function (form) {
                    if (form.dataset.coinmealBound === '1') return;
                    form.dataset.coinmealBound = '1';
                    form.addEventListener('submit', function (e) {
                        if (form.dataset.coinmealConfirmed === '1') {
                            delete form.dataset.coinmealConfirmed;
                            return;
                        }
                        e.preventDefault();
                        var msg = form.getAttribute('data-coinmeal-confirm') || '';
                        var ttl = form.getAttribute('data-coinmeal-confirm-title') || 'Please confirm';
                        var variant = form.getAttribute('data-coinmeal-confirm-variant') || 'danger';
                        var ok = form.getAttribute('data-coinmeal-confirm-ok') || 'Continue';
                        window.CoinmealDialog.confirm({
                            title: ttl,
                            message: msg,
                            variant: variant,
                            confirmLabel: ok,
                            cancelLabel: form.getAttribute('data-coinmeal-confirm-cancel') || 'Cancel',
                        }).then(function (confirmed) {
                            if (!confirmed) return;
                            form.dataset.coinmealConfirmed = '1';
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.submit();
                            }
                        });
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindConfirmForms);
            } else {
                bindConfirmForms();
            }
        })();
    </script>
@endonce
