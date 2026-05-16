@extends('admin.layout')

@section('title', 'Transactions')
@section('heading', 'All transactions')

@section('content')
    <div class="mb-4 rounded-xl bg-white p-4 shadow-sm">
        <form method="get" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Type</label>
                <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All types</option>
                    @foreach(['order' => 'Orders', 'wallet_load' => 'Wallet loads', 'coin_transfer' => 'Coin transfers', 'refund' => 'Refunds', 'payment' => 'Payments'] as $val => $label)
                        <option value="{{ $val }}" @selected(($filters['type'] ?? '') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600">Role</label>
                <select name="role" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All roles</option>
                    <option value="student" @selected(($filters['role'] ?? '') === 'student')>Student</option>
                    <option value="staff" @selected(($filters['role'] ?? '') === 'staff')>Staff</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Filter</button>
            <a href="{{ route('admin.transactions') }}" class="text-sm text-gray-500 underline">Clear</a>
        </form>
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm">
        @include('admin.partials.transactions-table', ['transactions' => $transactions])
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
@endsection
