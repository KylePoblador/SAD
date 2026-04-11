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

<body class="min-h-screen bg-gradient-to-br from-[#d8e6d8] via-[#cfe3d3] to-[#e7f1e7] px-4 py-8 font-sans relative overflow-hidden">
    @php
        $role = old('role', $selectedRole ?? request('role', 'student'));
        $isStaff = $role === 'staff';
        $headerColor = $isStaff ? 'from-orange-600 to-orange-500' : 'from-green-600 to-green-500';
        $buttonColor = $isStaff ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600';
    @endphp

    <div class="pointer-events-none absolute -top-16 -left-16 h-56 w-56 rounded-full bg-green-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-16 h-64 w-64 rounded-full bg-emerald-200/40 blur-3xl"></div>

    <div class="relative mx-auto w-full max-w-sm overflow-hidden rounded-3xl border border-white/70 bg-white/95 shadow-[0_20px_45px_rgba(28,77,43,0.18)] backdrop-blur">
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
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            placeholder="Enter your password"
                            class="w-full rounded-lg border border-gray-300 py-2 pl-3 pr-11 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500" />
                        <button type="button" data-password-toggle="password"
                            class="absolute right-1 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                            aria-label="Show password">
                            <svg class="pw-eye h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="pw-eye-off hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
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
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = btn.getAttribute('data-password-toggle');
                var input = document.getElementById(id);
                if (!input) return;
                var eye = btn.querySelector('.pw-eye');
                var eyeOff = btn.querySelector('.pw-eye-off');
                if (input.type === 'password') {
                    input.type = 'text';
                    eye && eye.classList.add('hidden');
                    eyeOff && eyeOff.classList.remove('hidden');
                    btn.setAttribute('aria-label', 'Hide password');
                } else {
                    input.type = 'password';
                    eye && eye.classList.remove('hidden');
                    eyeOff && eyeOff.classList.add('hidden');
                    btn.setAttribute('aria-label', 'Show password');
                }
            });
        });
    </script>
</body>

</html>
