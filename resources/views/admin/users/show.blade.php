@extends('admin.layout')

@section('title', $user->name)
@section('heading', 'User details')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 hover:underline">← Back to users</a>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Profile</h2>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-sm font-medium text-indigo-600 hover:underline">Edit profile</a>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Name</dt>
                    <dd class="font-medium text-right">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-right">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Role</dt>
                    <dd class="text-right uppercase">{{ $user->role }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">College</dt>
                    <dd class="text-right">{{ $user->college ? strtoupper($user->college) : '—' }}</dd>
                </div>
                @if($user->student_id)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Student ID</dt>
                        <dd class="text-right">{{ $user->student_id }}</dd>
                    </div>
                @endif
                @if($user->phone)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-right">{{ $user->phone }}</dd>
                    </div>
                @endif
                @if($user->role === 'staff' && $user->canteen_name)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Canteen</dt>
                        <dd class="text-right">{{ $user->canteen_name }}</dd>
                    </div>
                @endif
                @if($user->role === 'student')
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Wallet balance</dt>
                        <dd class="font-semibold text-right">₱{{ number_format((float) ($user->wallet_balance ?? 0), 2) }}</dd>
                    </div>
                @endif
                @if($user->last_login_at)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Last login</dt>
                        <dd class="text-right">{{ $user->last_login_at->format('M d, Y H:i') }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 pb-2">
                    <dt class="text-gray-500">Joined</dt>
                    <dd class="text-right">{{ $user->created_at?->format('M d, Y') ?? '—' }}</dd>
                </div>
            </dl>

            @if($user->is_inactive)
                <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">Labeled inactive by admin</p>
            @endif

            <form method="post" action="{{ route('admin.users.inactive.toggle', $user) }}" class="mt-4">
                @csrf
                <input type="hidden" name="is_inactive" value="{{ $user->is_inactive ? 0 : 1 }}">
                <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">
                    {{ $user->is_inactive ? 'Clear inactive label' : 'Label as inactive' }}
                </button>
            </form>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Activity summary</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg bg-blue-50 p-3">
                    <p class="text-xs text-blue-600">Orders</p>
                    <p class="text-xl font-bold text-blue-900">{{ $orderCount }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3">
                    <p class="text-xs text-emerald-600">Wallet loads</p>
                    <p class="text-xl font-bold text-emerald-900">{{ $walletLoadCount }}</p>
                </div>
                <div class="rounded-lg bg-violet-50 p-3">
                    <p class="text-xs text-violet-600">Transfers</p>
                    <p class="text-xl font-bold text-violet-900">{{ $transferCount }}</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-3">
                    <p class="text-xs text-amber-600">Refunds received</p>
                    <p class="text-xl font-bold text-amber-900">{{ $refundCount }}</p>
                </div>
            </div>

            @if($user->role === 'student')
                <h3 class="mt-6 mb-2 text-sm font-semibold text-gray-700">Spending summary</h3>
                <div class="grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-gray-50 p-3 border border-gray-100">
                        <p class="text-xs text-gray-500">Daily</p>
                        <p class="text-lg font-bold text-gray-900">₱{{ number_format($dailySpent, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 border border-gray-100">
                        <p class="text-xs text-gray-500">Weekly</p>
                        <p class="text-lg font-bold text-gray-900">₱{{ number_format($weeklySpent, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 border border-gray-100">
                        <p class="text-xs text-gray-500">Monthly</p>
                        <p class="text-lg font-bold text-gray-900">₱{{ number_format($monthlySpent, 2) }}</p>
                    </div>
                </div>
            @endif

            @if($canteenBalances->isNotEmpty())
                <h3 class="mb-2 mt-4 text-sm font-semibold text-gray-700">Per-canteen balance</h3>
                <ul class="space-y-1 text-sm">
                    @foreach($canteenBalances as $bal)
                        <li class="flex justify-between rounded bg-gray-50 px-2 py-1">
                            <span class="uppercase">{{ $bal->college }}</span>
                            <span class="font-semibold">₱{{ number_format((float) $bal->balance, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h2 class="text-lg font-semibold">Transaction history</h2>
            <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="flex flex-wrap items-center gap-2">
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="rounded-md border-gray-300 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <span class="text-gray-500 text-sm">to</span>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="rounded-md border-gray-300 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Filter</button>
                @if(($filters['start_date'] ?? null) || ($filters['end_date'] ?? null))
                    <a href="{{ route('admin.users.show', $user->id) }}" class="text-sm text-gray-500 hover:underline">Clear</a>
                @endif
            </form>
        </div>
        @include('admin.partials.transactions-table', ['transactions' => $recentTransactions])
        <a href="{{ route('admin.transactions', ['role' => $user->role]) }}" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">Browse all {{ $user->role }} transactions →</a>
    </div>
@endsection
