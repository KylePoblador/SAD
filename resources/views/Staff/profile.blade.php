<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff profile — CoinMeal</title>
    @include('partials.coinmeal-assets')
</head>

<body class="min-h-screen bg-gray-100 pb-24 font-sans text-gray-900 antialiased">

    <header
        class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3 shadow-sm">
        <div class="min-w-0">
            <div class="mb-1">
                @include('partials.app-back-link', ['href' => route('staff.dashboard'), 'variant' => 'staff'])
            </div>
            <h1 class="text-lg font-bold text-green-600">{{ $staffCanteenName ?? 'Canteen staff' }}</h1>
            <p class="text-xs text-gray-500">Staff profile</p>
            @if ($staffCollegeCode)
                <p class="mt-0.5 text-[10px] text-gray-400">{{ $staffCollegeCode }}</p>
            @endif
        </div>
        <a href="{{ route('staff.notification') }}" class="relative text-gray-500 hover:text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span id="staff-unread-badge"
                class="absolute -right-2 -top-2 hidden h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">0</span>
        </a>
    </header>

    <div class="coinmeal-container space-y-4 py-4 sm:space-y-5">

        <h2 class="text-base font-bold text-gray-800">Profile</h2>

        {{-- Avatar Card --}}
        <div class="flex flex-col items-center rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            @php
                $nameParts = explode(' ', Auth::user()->name);
                $initials =
                    strtoupper(substr($nameParts[0], 0, 1)) .
                    (isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '');
            @endphp
            @if (Auth::user()->avatarPublicUrl())
                <img src="{{ Auth::user()->avatarPublicUrl() }}" alt=""
                    class="mb-3 h-16 w-16 rounded-full object-cover ring-2 ring-green-100" />
            @else
                <div
                    class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-green-600 text-xl font-bold text-white">
                    {{ $initials }}
                </div>
            @endif
            <p class="font-bold text-gray-800 text-base">{{ Auth::user()->name }}</p>
            <p class="text-xs text-gray-500">Canteen Staff</p>
            <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
        </div>

        {{-- Edit Profile Form --}}
        <div class="space-y-3 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Personal Information</p>

            @if (session('status') === 'profile-updated')
                <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                    <p class="text-xs text-green-600 font-semibold">Profile updated successfully!</p>
                </div>
            @endif

            <form method="POST" action="{{ route('staff.profile.update') }}" enctype="multipart/form-data"
                class="space-y-3">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-xs text-gray-500">Profile photo</label>
                    <input type="file" name="avatar" accept="image/*"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-green-700" />
                    @error('avatar')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] text-gray-400">Select Photo</p>
                </div>

                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                </div>

                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Email</label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
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
                    class="w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    Save changes
                </button>
            </form>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full rounded-xl bg-red-50 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                Log out
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

    <script>
        (function() {
            const badge = document.getElementById('staff-unread-badge');
            const endpoint = @json(route('staff.unread-count'));
            if (!badge || !endpoint) return;

            async function updateStaffUnreadBadge() {
                try {
                    const response = await fetch(endpoint, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                        },
                    });
                    const data = await response.json();
                    const count = Number(data.unread_count || 0);
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : String(count);
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                } catch (e) {
                    // Ignore badge polling errors.
                }
            }

            updateStaffUnreadBadge();
            setInterval(updateStaffUnreadBadge, 5000);
        })();
    </script>
</body>

</html>
