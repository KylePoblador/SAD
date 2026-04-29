<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin dashboard</title>
    @include('partials.coinmeal-assets')
</head>
<body class="bg-gray-50 text-gray-900">
<div class="mx-auto max-w-6xl p-6">
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
    @endif
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Admin dashboard</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.coupons') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Manage coupons</a>
            <a href="{{ route('force-logout') }}" class="text-sm text-gray-500 underline">Logout</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Total users</p><p class="text-2xl font-bold">{{ $totalUsers }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Students</p><p class="text-2xl font-bold">{{ $studentCount }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Staff</p><p class="text-2xl font-bold">{{ $staffCount }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Admins</p><p class="text-2xl font-bold">{{ $adminCount }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Orders</p><p class="text-2xl font-bold">{{ $totalOrders }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Active coupons</p><p class="text-2xl font-bold">{{ $activeCoupons }}</p></div>
        <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Inactive users (auto/manual)</p><p class="text-2xl font-bold">{{ $inactiveCount }}</p></div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Recent users and activity</h2>
        <div class="space-y-2 text-sm">
            @foreach($latestUsers as $u)
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-2">
                    <div>
                        <p>{{ $u->name }} ({{ $u->email }})</p>
                        <p class="text-xs text-gray-500">
                            Last active:
                            {{ $u->last_active_at ? $u->last_active_at->format('M d, Y H:i') : 'Never' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($u->is_auto_inactive || $u->is_inactive)
                            <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Inactive</span>
                        @endif
                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs uppercase">{{ $u->role }}</span>
                        <form method="post" action="{{ route('admin.users.inactive.toggle', $u->id) }}">
                            @csrf
                            <input type="hidden" name="is_inactive" value="{{ $u->is_inactive ? 0 : 1 }}">
                            <button class="rounded bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-700">
                                {{ $u->is_inactive ? 'Clear label' : 'Label inactive' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
</body>
</html>
