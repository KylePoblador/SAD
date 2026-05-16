@extends('admin.layout')

@section('title', $user->name)
@section('heading', 'User details')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 hover:underline">← Back to users</a>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Profile</h2>
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
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Wallet balance</dt>
                    <dd class="font-semibold text-right">₱{{ number_format((float) ($user->wallet_balance ?? 0), 2) }}</dd>
                </div>
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
        <h2 class="mb-3 text-lg font-semibold">Transaction history</h2>
        @include('admin.partials.transactions-table', ['transactions' => $recentTransactions])
        <a href="{{ route('admin.transactions', ['role' => $user->role]) }}" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">Browse all {{ $user->role }} transactions →</a>
    </div>
@endsection
