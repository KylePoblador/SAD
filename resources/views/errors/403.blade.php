<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 | Forbidden</title>
    @include('partials.coinmeal-assets')
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <main class="coinmeal-container flex min-h-screen items-center justify-center py-8">
        <div class="w-full max-w-md rounded-2xl border border-red-200 bg-white p-6 text-center shadow-sm">
            <p class="text-sm font-semibold text-red-600">403 Forbidden</p>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">You do not have access to this page.</h1>
            <p class="mt-2 text-sm text-gray-600">Please sign in with an admin account to open the Admin Dashboard.</p>
            <a href="{{ route('dashboard') }}"
                class="mt-5 inline-flex rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                Back to dashboard
            </a>
        </div>
    </main>
</body>
</html>
