<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoinMeal Notifications</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f3f4f6;
        }

        .notification {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 15px;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .notification.unread {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }

        .notification.green {
            background-color: #dff0d8;
            border-color: #b2d8b2;
        }

        .icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .icon.orange {
            background: #f39c12;
            color: white;
        }

        .icon.pink {
            background: #f5b7b1;
        }

        .text {
            flex: 1;
        }

        .text strong {
            display: block;
            font-size: 14px;
        }

        .time {
            font-size: 12px;
            color: gray;
        }

        .unread-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #4caf50;
            border-radius: 50%;
            margin-left: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.preparing {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.ready {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .streaming-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            background: #e3f2fd;
            border-radius: 4px;
            font-size: 12px;
            color: #1976d2;
            margin-right: 10px;
        }

        .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #1976d2;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="min-h-screen pb-20">

    {{-- HEADER --}}
    <div class="bg-white px-4 py-3 flex items-center justify-between shadow-sm sticky top-0 z-10">
        <div>
            <h1 class="text-lg font-bold text-green-600">CoinMeal</h1>
            <p class="text-xs text-gray-500">University of Southern Mindanao</p>
        </div>
        <div class="flex items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
        </div>
    </div>

    {{-- TITLE --}}
    <div class="px-4 py-4 flex items-center justify-between">
        <h2 class="text-base font-bold text-gray-800">Notifications</h2>
        <div class="streaming-indicator">
            <span class="dot"></span>
            <span>Live</span>
        </div>
    </div>

    {{-- NOTIFICATIONS --}}
    <div id="notification-list" class="space-y-3">
        <div class="notification">
            <div class="icon orange">⏳</div>
            <div class="text">
                <strong>Loading notifications...</strong>
                <div class="time">Please wait</div>
            </div>
        </div>
    </div>

    <script>
        const list = document.getElementById('notification-list');
        const streamEndpoint = '{{ route('student.notification.stream') }}';
        const markReadEndpoint = '{{ route('student.notification.mark-read', ':orderId') }}';

        let eventSource = null;
        let lastNotifications = [];

        function makeNotificationHtml(notification) {
            const colorClass = notification.status === 'ready' || notification.status === 'completed' ?
                'green' : notification.status === 'empty' ? '' : 'orange';

            const unreadClass = !notification.is_read && notification.status !== 'empty' ? 'unread' : '';
            const unreadBadge = !notification.is_read && notification.status !== 'empty' ?
                '<span class="unread-badge"></span>' : '';

            return `
                <div class="notification ${colorClass} ${unreadClass}" data-order-id="${notification.id}" onclick="markAsRead(${notification.id})">
                    <div class="icon ${notification.status === 'empty' ? 'pink' : ''}">${notification.status === 'empty' ? 'ℹ️' : '🛒'}</div>
                    <div class="text">
                        <div style="display: flex; align-items: center;">
                            <strong>${notification.title}</strong>
                            ${unreadBadge}
                        </div>
                        <div class="time">${notification.time}</div>
                        <div class="text-sm text-gray-500">${notification.message}</div>
                        <span class="status-badge ${notification.status}">${notification.status.toUpperCase()}</span>
                    </div>
                </div>
            `;
        }

        async function markAsRead(orderId) {
            if (orderId === null) return;

            try {
                await fetch(markReadEndpoint.replace(':orderId', orderId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                });
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        function connectStream() {
            if (eventSource) {
                eventSource.close();
            }

            eventSource = new EventSource(streamEndpoint);

            eventSource.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    lastNotifications = data.notifications || [];

                    if (lastNotifications.length === 0 || (lastNotifications.length === 1 && lastNotifications[0]
                            .status === 'empty')) {
                        list.innerHTML = lastNotifications.map(makeNotificationHtml).join('');
                    } else {
                        list.innerHTML = lastNotifications.map(makeNotificationHtml).join('');
                    }
                } catch (error) {
                    console.error('Error parsing notification data:', error);
                }
            };

            eventSource.onerror = function(error) {
                console.error('SSE connection error:', error);
                eventSource.close();

                // Fallback to polling after 2 seconds
                setTimeout(connectStream, 2000);
            };
        }

        // Start real-time stream
        connectStream();

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (eventSource) {
                eventSource.close();
            }
        });
    </script>

    {{-- BOTTOM NAVIGATION --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-3 z-10">
        <a href="{{ route('student.dashboard') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Home
        </a>
        <a href="#" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Order
        </a>
        <a href="{{ route('student.profile') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
    </div>

</body>

</html>
