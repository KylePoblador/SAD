@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Students & staff</p>
            <p class="text-2xl font-bold">{{ $totalUsers }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Students</p>
            <p class="text-2xl font-bold">{{ $studentCount }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Staff</p>
            <p class="text-2xl font-bold">{{ $staffCount }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Orders</p>
            <p class="text-2xl font-bold">{{ $totalOrders }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Active coupons</p>
            <p class="text-2xl font-bold">{{ $activeCoupons }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Labeled inactive</p>
            <p class="text-2xl font-bold">{{ $inactiveCount }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Admins</p>
            <p class="text-2xl font-bold">{{ $adminCount }}</p>
        </div>
        <a href="{{ route('admin.transactions') }}" class="flex items-center rounded-xl bg-indigo-600 p-4 text-white shadow-sm hover:bg-indigo-700">
            <span class="text-sm font-semibold">View all transactions →</span>
        </a>
    </div>

    <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent transactions</h2>
            <a href="{{ route('admin.transactions') }}" class="text-sm font-medium text-indigo-600 hover:underline">See all</a>
        </div>
        @include('admin.partials.transactions-table', ['transactions' => $recentTransactions])
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Users (students & staff)</h2>
            <a href="{{ route('admin.users') }}" class="text-sm font-medium text-indigo-600 hover:underline">Manage users</a>
        </div>
        <div class="space-y-2 text-sm">
            @foreach($latestUsers as $u)
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-2">
                    <div>
                        <a href="{{ route('admin.users.show', $u->id) }}" class="font-medium text-indigo-600 hover:underline">{{ $u->name }}</a>
                        <p class="text-xs text-gray-500">{{ $u->email }} · Last active: {{ $u->last_active_at ? $u->last_active_at->format('M d, Y H:i') : 'Never' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($u->is_auto_inactive || $u->is_inactive)
                            <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Inactive</span>
                        @endif
                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs uppercase">{{ $u->role }}</span>
                        <a href="{{ route('admin.users.show', $u->id) }}" class="rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800 hover:bg-indigo-200">View details</a>
                        <form method="post" action="{{ route('admin.users.inactive.toggle', $u->id) }}">
                            @csrf
                            <input type="hidden" name="is_inactive" value="{{ $u->is_inactive ? 0 : 1 }}">
                            <button type="submit" class="rounded bg-gray-800 px-2 py-1 text-xs font-semibold text-white hover:bg-gray-900">
                                {{ $u->is_inactive ? 'Clear label' : 'Label inactive' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
