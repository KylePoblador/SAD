<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CoinMeal - Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>

<body class="min-h-screen bg-[#2f3136] px-4 py-8 font-sans">
    @php
        $role = old('role', $selectedRole ?? request('role', 'student'));
        $isStaff = $role === 'staff';
        $headerColor = $isStaff ? 'from-orange-600 to-orange-500' : 'from-green-600 to-green-500';
        $buttonColor = $isStaff ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600';
    @endphp

    <div class="mx-auto w-full max-w-sm overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl">
        <div class="bg-gradient-to-b {{ $headerColor }} px-5 py-5 text-white">
            <div class="mb-4">
                <a href="{{ url('/') }}"
                    class="inline-flex items-center text-sm font-medium text-white/90 hover:text-white">←</a>
            </div>
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                <span class="text-xl">🍽️</span>
            </div>
            <h1 class="text-center text-3xl font-bold">Welcome to CoinMeal</h1>
            <p class="mt-1 text-center text-sm text-white/90">Sign in as {{ $isStaff ? 'Canteen Staff' : 'Student' }}
            </p>
        </div>

        <div class="px-5 py-6">
            <x-auth-session-status
                class="mb-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800"
                :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" placeholder="Enter your email"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <label for="password" class="mb-1 block text-xs font-semibold text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        placeholder="Enter your password"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    @if (Route::has('password.request'))
                        <div class="mt-2 text-right">
                            <a class="text-xs font-semibold text-green-700 hover:underline"
                                href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                    @endif
                </div>

                <button type="submit"
                    class="w-full rounded-xl px-4 py-3 text-sm font-semibold text-white {{ $buttonColor }}">
                    Login
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register', ['role' => $role]) }}"
                    class="font-semibold text-green-700 hover:underline">Create an account</a>
            </p>
        </div>
    </div>
</body>

</html>
