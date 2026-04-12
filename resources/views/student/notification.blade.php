<x-layouts.student title="Notifications" active="none">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center justify-between gap-2 sm:justify-start">
            <h2 class="text-base font-bold text-gray-800">Notifications</h2>
            <div class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 sm:hidden">
                <span class="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-blue-600"></span>
                Live
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="hidden items-center gap-2 rounded-lg bg-blue-50 px-2 py-1 text-xs font-medium text-blue-800 sm:inline-flex">
                <span class="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-blue-600"></span>
                Live
            </div>
            <button type="button" id="student-notif-mark-all"
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                Mark all as done
            </button>
            <button type="button" id="student-notif-clear-all"
                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">
                Clear all
            </button>
        </div>
    </div>

    <div id="notification-list" class="space-y-3">
        <div
            class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">⏳
            </div>
            <div class="min-w-0 flex-1">
                <strong class="block text-sm text-gray-900">Loading notifications…</strong>
                <div class="text-xs text-gray-500">Please wait</div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const list = document.getElementById('notification-list');
            const streamEndpoint = @json(route('student.notification.stream'));
            const markReadUrl = @json(route('student.notification.mark-read'));
            const markAllReadUrl = @json(route('student.notification.mark-all-read'));
            const clearAllUrl = @json(route('student.notification.clear-all'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const notificationFallbackUrl = @json(route('student.notification', [], false));

            let eventSource = null;

            function badgeClass(status) {
                const s = (status || '').toLowerCase();
                if (s === 'pending') return 'bg-yellow-100 text-yellow-900';
                if (s === 'preparing') return 'bg-orange-100 text-orange-900';
                if (s === 'ready') return 'bg-green-100 text-green-800';
                if (s === 'completed') return 'bg-green-600 text-white';
                if (s === 'info') return 'bg-slate-100 text-slate-700';
                return 'bg-gray-100 text-gray-600';
            }

            function iconEmoji(icon, status) {
                if (status === 'empty') return 'ℹ️';
                if (icon === 'wallet') return '💰';
                if (icon === 'seat') return '🪑';
                if (icon === 'order') return '🛒';
                return '🔔';
            }

            function iconBg(icon, status) {
                if (status === 'empty') return 'bg-rose-200 text-rose-900';
                if (icon === 'wallet') return 'bg-amber-500 text-white';
                if (icon === 'seat') return 'bg-sky-500 text-white';
                if (icon === 'order') {
                    const st = (status || '').toLowerCase();
                    if (st === 'completed') return 'bg-green-600 text-white';
                    if (st === 'ready') return 'bg-green-400 text-white';
                    if (st === 'preparing') return 'bg-orange-500 text-white';
                    if (st === 'pending') return 'bg-yellow-500 text-yellow-950';
                }
                return 'bg-violet-500 text-white';
            }

            function escAttr(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
            }

            function makeNotificationHtml(notification) {
                const nid = notification.nid || 'none';
                const isEmpty = notification.status === 'empty';
                const isUnread = !isEmpty && !notification.is_read;
                const shell = isUnread ?
                    'border-l-4 border-green-500 bg-green-50/80 border-y border-r border-gray-200' :
                    'border border-gray-200 bg-white';
                const emoji = iconEmoji(notification.icon, notification.status);
                const ibg = iconBg(notification.icon, notification.status);
                const unreadDot = isUnread ?
                    '<span class="ml-2 inline-block h-2 w-2 rounded-full bg-green-500"></span>' : '';
                const statusLabel = (notification.status || '').toUpperCase();
                const actionUrl = String(notification.action_url || notification.actionUrl || '').trim();
                const href = actionUrl || notificationFallbackUrl;
                const cursorClass = isEmpty ? '' : 'cursor-pointer';
                const inner = `
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-sm ${ibg}">${emoji}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center">
                            <strong class="text-sm text-gray-900">${notification.title}</strong>
                            ${unreadDot}
                        </div>
                        <div class="text-xs text-gray-500">${notification.time}</div>
                        <div class="mt-1 text-sm text-gray-600">${notification.message}</div>
                        <span class="mt-2 inline-block rounded px-2 py-0.5 text-[11px] font-bold ${badgeClass(notification.status)}">${statusLabel}</span>
                    </div>`;

                if (isEmpty) {
                    return `
                <div role="status" class="flex items-start gap-3 rounded-xl p-3 shadow-sm ${shell}" data-nid="${escAttr(nid)}" data-status="empty">
                    ${inner}
                </div>`;
                }

                return `
                <a href="${escAttr(href)}" class="notification-feed-item flex items-start gap-3 rounded-xl p-3 shadow-sm transition hover:shadow-md ${shell} ${cursorClass} text-inherit no-underline" data-nid="${escAttr(nid)}" data-status="${escAttr(notification.status)}">
                    ${inner}
                </a>`;
            }

            function markAsRead(nid) {
                if (!nid || nid === 'none') {
                    return Promise.resolve();
                }
                return fetch(markReadUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nid
                    }),
                }).catch(function() {});
            }

            if (!list.dataset.clickBound) {
                list.dataset.clickBound = '1';
                list.addEventListener('click', function(e) {
                    const row = e.target.closest('a.notification-feed-item');
                    if (!row) return;
                    if (row.getAttribute('data-status') === 'empty') return;
                    const nid = row.getAttribute('data-nid');
                    if (!nid || nid === 'none') return;
                    e.preventDefault();
                    const target = row.href;
                    Promise.race([
                        markAsRead(nid),
                        new Promise(function(r) {
                            setTimeout(r, 400);
                        }),
                    ]).catch(function() {}).finally(function() {
                        window.location.assign(target);
                    });
                });
            }

            function renderNotes(notes) {
                list.innerHTML = (notes || []).map(makeNotificationHtml).join('');
            }

            async function postNotificationAction(url) {
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }
                return data;
            }

            document.getElementById('student-notif-mark-all')?.addEventListener('click', async function() {
                try {
                    const data = await postNotificationAction(markAllReadUrl);
                    if (data.notifications) {
                        renderNotes(data.notifications);
                    }
                } catch (e) {
                    console.error(e);
                }
            });

            document.getElementById('student-notif-clear-all')?.addEventListener('click', async function() {
                if (!confirm('Clear all notifications from this list? New activity will still appear here.')) {
                    return;
                }
                try {
                    const data = await postNotificationAction(clearAllUrl);
                    if (data.notifications) {
                        renderNotes(data.notifications);
                    }
                } catch (e) {
                    console.error(e);
                }
            });

            function connectStream() {
                if (eventSource) {
                    eventSource.close();
                }
                eventSource = new EventSource(streamEndpoint);
                eventSource.onmessage = function(event) {
                    try {
                        const data = JSON.parse(event.data);
                        const notes = data.notifications || [];
                        renderNotes(notes);
                    } catch (error) {
                        console.error('Error parsing notification data:', error);
                    }
                };
                eventSource.onerror = function() {
                    eventSource.close();
                    setTimeout(connectStream, 2000);
                };
            }

            connectStream();
            window.addEventListener('beforeunload', function() {
                if (eventSource) {
                    eventSource.close();
                }
            });
        </script>
    @endpush
</x-layouts.student>
