<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CoinMeal') }}</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f5f5f5; font-family:Arial;">

    {{-- OPTIONAL NAV (remove if error) --}}
    {{-- @include('layouts.navigation') --}}

    <main class="py-3">
        @yield('content')
    </main>

</body>
</html>
