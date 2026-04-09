<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: #f3f4f6;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 pb-20">

    {{-- Header --}}
    <div class="bg-green-500 px-4 py-4 text-white sticky top-0 z-10">
        <div class="flex items-center justify-between max-w-lg mx-auto">
            <div>
                <h1 class="text-lg font-bold">CEIT Main Canteen</h1>
                <p class="text-xs opacity-85">Staff Profile</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white cursor-pointer" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-4 space-y-4">

        <h2 class="text-base font-bold text-gray-800">Profile</h2>

        {{-- Avatar Card --}}
        <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col items-center">
            @php
                $nameParts = explode(' ', Auth::user()->name);
                $initials =
                    strtoupper(substr($nameParts[0], 0, 1)) .
                    (isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '');
            @endphp
            <div
                class="w-16 h-16 rounded-full bg-green-500 flex items-center justify-center text-white text-xl font-bold mb-3">
                {{ $initials }}
            </div>
            <p class="font-bold text-gray-800 text-base">{{ Auth::user()->name }}</p>
            <p class="text-xs text-gray-500">Canteen Staff</p>
            <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
        </div>

        {{-- Edit Profile Form --}}
        <div class="bg-white rounded-xl p-5 shadow-sm space-y-3">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Personal Information</p>

            @if (session('status') === 'profile-updated')
                <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                    <p class="text-xs text-green-600 font-semibold">Profile updated successfully!</p>
                </div>
            @endif

            <form method="POST" action="{{ route('staff.profile.update') }}" class="space-y-3">
                @csrf
                @method('PATCH')

                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Full Name</label>
                    <input type="text" name="name" value="{{ Auth::user()->name }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>

                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Email</label>
                    <input type="email" name="email" value="{{ Auth::user()->email }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>

                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Canteen Name</label>
                    <input type="text" name="canteen_name" placeholder="e.g. CEIT Main Canteen"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>

                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Canteen Location</label>
                    <input type="text" name="canteen_location" placeholder="e.g. Near CECM Building"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>

                <button type="submit"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-xl text-sm">
                    Save Changes
                </button>
            </form>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full bg-red-100 hover:bg-red-200 text-red-500 font-semibold py-3 rounded-xl text-sm">
                Log Out
            </button>
        </form>

    </div>

    {{-- Bottom Navigation --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around py-3 z-10">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            Dashboard
        </a>
        <a href="{{ route('staff.orders') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Orders
        </a>
        <a href="{{ route('staff.menu') }}" class="flex flex-col items-center text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Menu
        </a>
        <a href="{{ route('staff.profile') }}" class="flex flex-col items-center text-xs text-green-600 font-semibold">
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
