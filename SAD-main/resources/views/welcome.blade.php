<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CoinMeal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-[#d8e6d8] font-[Instrument_Sans,_ui-sans-serif,_system-ui] text-[#243229]">
    <main class="mx-auto flex min-h-screen w-full max-w-4xl items-center justify-center px-4 py-8 sm:px-6">
        <section class="w-full max-w-[450px] text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-[#53b36a] shadow-[0_10px_25px_rgba(23,77,34,0.2)]">
                <span class="text-4xl leading-none">🍽️</span>
            </div>

            <h1 class="text-5xl font-bold tracking-tight text-[#1f2a1f]">CoinMeal</h1>
            <p class="mt-4 text-base text-[#6b7b6f]">Choose how you'd like to continue</p>

            <div class="mt-10 space-y-4 text-left">
                <a href="{{ route('login', ['role' => 'student']) }}" class="block rounded-2xl border border-[#95cfa2] bg-[#a9d5ae] px-5 py-4 shadow-[0_6px_14px_rgba(46,112,57,0.12)] transition hover:-translate-y-0.5">
                    <div class="flex items-start gap-4">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#5db56e] text-white">👤</span>
                        <span>
                            <span class="block text-2xl font-semibold text-[#223726]">Student</span>
                            <span class="mt-1 block text-sm text-[#4b6751]">Browse menu, order food and manage wallet.</span>
                        </span>
                    </div>
                </a>

                <a href="{{ route('login', ['role' => 'staff']) }}" class="block rounded-2xl border border-[#ecc084] bg-[#efce9d] px-5 py-4 shadow-[0_6px_14px_rgba(131,87,20,0.12)] transition hover:-translate-y-0.5">
                    <div class="flex items-start gap-4">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f0a623] text-white">✓</span>
                        <span>
                            <span class="block text-2xl font-semibold text-[#3a2f1b]">Canteen Staff</span>
                            <span class="mt-1 block text-sm text-[#6f5c3a]">Manage orders, menu &amp; inventory.</span>
                        </span>
                    </div>
                </a>
            </div>

            @if (Route::has('register'))
                <p class="mt-10 text-sm text-[#6f7e72]">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-[#4aa160] hover:underline">Register here</a>
                </p>
            @endif
        </section>
    </main>
</body>
</html>
