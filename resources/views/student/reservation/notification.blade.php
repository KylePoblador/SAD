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
            <a href="{{ route('student.notification') }}" class="text-gray-500 hover:text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13L5.4 5M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
        </div>
    </div>

    {{-- TITLE --}}
    <div class="px-4 py-4">
        <h2 class="text-base font-bold text-gray-800">Notifications</h2>
    </div>

    {{-- NOTIFICATIONS --}}
    <div class="notification green">
        <div class="icon orange">🛒</div>
        <div class="text">
            <strong>Your order #ORD-001 is ready for pickup!</strong>
            <div class="time">2 mins ago</div>
        </div>
    </div>

    <div class="notification green">
        <div class="icon">⏰</div>
        <div class="text">
            <strong>Your reserved seat expires in 10 minutes</strong>
            <div class="time">15 mins ago</div>
        </div>
    </div>

    <div class="notification">
        <div class="icon pink">⭐</div>
        <div class="text">
            <strong>Buy 2 Get 1 Free on all Beverages today!</strong>
            <div class="time">1 hour ago</div>
        </div>
    </div>

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
        <a href="{{ route('student.notification') }}" class="flex flex-col items-center text-xs text-gray-400">
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
