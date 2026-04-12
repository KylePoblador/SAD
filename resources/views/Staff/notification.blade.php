<x-layouts.staff-subpage title="Staff notifications" subtitle="Orders and other activity">
    <div class="mb-4 flex flex-wrap gap-2">
        <button type="button" id="staff-notif-mark-all"
            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            Mark all as done
        </button>
        <button type="button" id="staff-notif-clear-all"
            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100">
            Clear all
        </button>
    </div>

    <div id="notification-list" class="space-y-3">
        <div
            class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-lg text-white">⏳
            </div>
            <div class="min-w-0 flex-1">
                <strong class="block text-sm text-gray-900">Loading notifications…</strong>
                <div class="text-xs text-gray-500">Please wait</div>
            </div>
        </div>
    </div>

    <script>
        const list = document.getElementById('notification-list');
        const endpoint = @json(route('staff.notification.data'));
        const markReadUrl = @json(route('staff.notification.mark-read'));
        const markAllReadUrl = @json(route('staff.notification.mark-all-read'));
        const clearAllUrl = @json(route('staff.notification.clear-all'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const notificationFallbackUrl = @json(route('staff.notification', [], false));

        function shellClass(status, isRead) {
            if (status === 'empty') return 'border border-gray-200 bg-white';
            const unread = !isRead && status !== 'empty';
            if (unread) return 'border-l-4 border-rose-500 bg-rose-50/70 border-y border-r border-gray-200';
            if (status === 'completed') return 'border border-green-300 bg-green-50/90';
            if (status === 'ready') return 'border border-green-200 bg-green-100/60';
            if (status === 'preparing') return 'border border-orange-200 bg-orange-50/90';
            if (status === 'pending') return 'border border-yellow-200 bg-yellow-50/90';
            if (status === 'info') return 'border border-slate-200 bg-slate-50/90';
            return 'border border-gray-200 bg-white';
        }

        function iconClass(icon, status) {
            if (status === 'empty') return 'bg-rose-200 text-rose-900';
            if (icon === 'wallet') return 'bg-amber-500 text-white';
            if (icon === 'seat') return 'bg-sky-500 text-white';
            if (status === 'completed') return 'bg-green-600 text-white';
            if (status === 'ready') return 'bg-green-400 text-white';
            if (status === 'preparing') return 'bg-orange-500 text-white';
            if (status === 'pending') return 'bg-yellow-500 text-yellow-950';
            return 'bg-gray-200 text-gray-800';
        }

        function iconEmoji(icon, status) {
            if (status === 'empty') return 'ℹ️';
            if (icon === 'wallet') return '💰';
            if (icon === 'seat') return '🪑';
            if (icon === 'order') {
                if (status === 'ready' || status === 'completed') return '🍽️';
                return '⏳';
            }
            return '🔔';
        }

        function markStaffReadRequest(nid) {
            if (!nid || nid === 'none') {
                return Promise.resolve();
            }
            if (!/^(a|o):/.test(String(nid))) {
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

        function escAttr(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
        }

        function renderNotification(n) {
            const nid = n.nid || 'none';
            const isEmpty = n.status === 'empty';
            const isRead = !!n.is_read;
            const icon = iconEmoji(n.icon, n.status);
            const ic = iconClass(n.icon, n.status);
            const actionUrl = String(n.action_url || n.actionUrl || '').trim();
            const href = actionUrl || notificationFallbackUrl;
            const cur = !isEmpty ? 'cursor-pointer' : '';
            const shell = shellClass(n.status, isRead);
            const inner = `
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-lg ${ic}">${icon}</div>
                    <div class="min-w-0 flex-1">
                        <strong class="block text-sm text-gray-900">${n.title}</strong>
                        <div class="text-xs text-gray-500">${n.time}</div>
                        <div class="mt-1 text-sm text-gray-600">${n.message}</div>
                    </div>`;

            if (isEmpty) {
                return `
                <div role="status" class="flex items-start gap-3 rounded-xl p-3 shadow-sm ${shell}" data-nid="${escAttr(nid)}" data-status="empty">
                    ${inner}
                </div>`;
            }

            return `
                <a href="${escAttr(href)}" class="notification-feed-item flex items-start gap-3 rounded-xl p-3 shadow-sm transition hover:shadow-md ${shell} ${cur} text-inherit no-underline" data-nid="${escAttr(nid)}" data-status="${escAttr(n.status)}">
                    ${inner}
                </a>`;
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
                    markStaffReadRequest(nid),
                    new Promise(function(r) {
                        setTimeout(r, 400);
                    }),
                ]).catch(function() {}).finally(function() {
                    window.location.assign(target);
                });
            });
        }

        async function postStaffNotificationAction(url) {
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

        document.getElementById('staff-notif-mark-all')?.addEventListener('click', async function() {
            try {
                const data = await postStaffNotificationAction(markAllReadUrl);
                if (data.notifications) {
                    list.innerHTML = data.notifications.map(renderNotification).join('');
                }
            } catch (e) {
                console.error(e);
            }
        });

        document.getElementById('staff-notif-clear-all')?.addEventListener('click', async function() {
            if (!confirm('Clear all notifications from this list? New activity will still appear here.')) {
                return;
            }
            try {
                const data = await postStaffNotificationAction(clearAllUrl);
                if (data.notifications) {
                    list.innerHTML = data.notifications.map(renderNotification).join('');
                }
            } catch (e) {
                console.error(e);
            }
        });

        async function loadNotifications() {
            try {
                const response = await fetch(endpoint, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                list.innerHTML = data.notifications.map(renderNotification).join('');
            } catch (error) {
                list.innerHTML = `
                <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3 shadow-sm">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-red-600 text-lg text-white">⚠️</div>
                    <div>
                        <strong class="block text-sm text-gray-900">Unable to load notifications</strong>
                        <div class="text-xs text-gray-500">Check your connection</div>
                    </div>
                </div>`;
            }
        }

        loadNotifications();
        setInterval(loadNotifications, 5000);
    </script>

    <div class="h-14" aria-hidden="true"></div>

    <nav class="fixed bottom-0 left-0 right-0 z-10 flex justify-around border-t border-gray-200 bg-white py-3">
        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-green-600">Dashboard</a>
        <a href="{{ route('staff.orders') }}" class="text-xs text-gray-400">Orders</a>
        <a href="{{ route('staff.profile') }}" class="text-xs text-gray-400">Profile</a>
    </nav>
</x-layouts.staff-subpage>
