<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoinMeal Staff Notifications</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #fff;
            border-bottom: 1px solid #ccc;
        }

        .logo {
            font-weight: bold;
            font-size: 22px;
            color: #2ecc71;
        }

        .subtext {
            font-size: 10px;
            color: gray;
        }

        .icons {
            font-size: 18px;
        }

        /* TITLE */
        .title {
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
        }

        /* NOTIFICATION CARD */
        .notification {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 10px 15px;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
        }

        /* TYPES */
        .success {
            background-color: #d4edda;
            border-color: #a5d6a7;
        }

        .warning {
            background-color: #fff3cd;
            border-color: #ffe082;
        }

        .alert {
            background-color: #f8d7da;
            border-color: #ef9a9a;
        }

        /* ICON */
        .icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
        }

        .icon.green {
            background: #27ae60;
            color: #fff;
        }

        .icon.orange {
            background: #f39c12;
            color: #fff;
        }

        .icon.red {
            background: #e74c3c;
            color: #fff;
        }

        /* TEXT */
        .text {
            flex: 1;
        }

        .text strong {
            font-size: 14px;
        }

        .time {
            font-size: 12px;
            color: gray;
        }

        /* BUTTON */
        .btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }

        /* NAV */
        .nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #fff;
            border-top: 1px solid #ccc;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
        }

        .nav div {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <div>
            <div class="logo">CoinMeal</div>
            <div class="subtext">Staff Panel</div>
        </div>
        <div class="icons">
            🔔 ⚙️
        </div>
    </div>

    <!-- TITLE -->
    <div class="title">Staff Notifications</div>

    <!-- NOTIFICATIONS -->
    <div id="notification-list">
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
        const endpoint = '{{ route('staff.notification.data') }}';

        function renderNotification(notification) {
            const classes = notification.status === 'ready' || notification.status === 'completed' ?
                'notification success' :
                notification.status === 'pending' || notification.status === 'preparing' ?
                'notification warning' :
                notification.status === 'empty' ?
                'notification' :
                'notification';

            const icon = notification.status === 'ready' || notification.status === 'completed' ?
                '🍽️' :
                notification.status === 'pending' || notification.status === 'preparing' ?
                '⏳' :
                notification.status === 'empty' ?
                'ℹ️' :
                '⭐';

            return `
                <div class="${classes}">
                    <div class="icon ${notification.status === 'ready' || notification.status === 'completed' ? 'green' : notification.status === 'pending' || notification.status === 'preparing' ? 'orange' : notification.status === 'empty' ? '' : ''}">${icon}</div>
                    <div class="text">
                        <strong>${notification.title}</strong>
                        <div class="time">${notification.time}</div>
                        <div class="time">${notification.message}</div>
                    </div>
                </div>
            `;
        }

        async function loadNotifications() {
            try {
                const response = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                list.innerHTML = data.notifications.map(renderNotification).join('');
            } catch (error) {
                list.innerHTML = `
                    <div class="notification alert">
                        <div class="icon red">⚠️</div>
                        <div class="text">
                            <strong>Unable to load notifications</strong>
                            <div class="time">Check your connection</div>
                        </div>
                    </div>
                `;
            }
        }

        loadNotifications();
        setInterval(loadNotifications, 5000);
    </script>

    <!-- NAV -->
    <div class="nav">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('staff.orders') }}">Orders</a>
        <a href="{{ route('staff.profile') }}">Profile</a>
    </div>

</body>

</html>
