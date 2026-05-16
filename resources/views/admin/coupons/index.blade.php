@extends('admin.layout')

@section('title', 'Coupons')
@section('heading', 'Coupons')

@section('content')
    <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Create coupon</h2>
        <form method="post" action="{{ route('admin.coupons.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
            @csrf
            <input name="code" placeholder="Code" class="rounded border border-gray-200 px-3 py-2 text-sm" required>
            <select name="type" class="rounded border border-gray-200 px-3 py-2 text-sm">
                <option value="fixed">Fixed</option>
                <option value="percent">Percent</option>
            </select>
            <input name="value" type="number" step="0.01" min="0.01" placeholder="Value" class="rounded border border-gray-200 px-3 py-2 text-sm" required>
            <input name="min_order_total" type="number" step="0.01" min="0" placeholder="Min order total" class="rounded border border-gray-200 px-3 py-2 text-sm">
            <input name="usage_limit" type="number" min="1" placeholder="Usage limit" class="rounded border border-gray-200 px-3 py-2 text-sm">
            <button type="submit" class="rounded bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save coupon</button>
        </form>
    </div>

    <div class="space-y-3">
        @foreach($coupons as $coupon)
            <form method="post" action="{{ route('admin.coupons.update', $coupon) }}" class="rounded-xl bg-white p-4 shadow-sm">
                @csrf
                @method('PATCH')
                <div class="mb-2 flex items-center justify-between">
                    <p class="font-semibold">{{ $coupon->code }}</p>
                    <span class="text-xs text-gray-500">Used {{ $coupon->used_count }} times</span>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                    <select name="type" class="rounded border border-gray-200 px-3 py-2 text-sm">
                        <option value="fixed" @selected($coupon->type==='fixed')>Fixed</option>
                        <option value="percent" @selected($coupon->type==='percent')>Percent</option>
                    </select>
                    <input name="value" type="number" step="0.01" min="0.01" value="{{ $coupon->value }}" class="rounded border border-gray-200 px-3 py-2 text-sm">
                    <input name="min_order_total" type="number" step="0.01" min="0" value="{{ $coupon->min_order_total }}" class="rounded border border-gray-200 px-3 py-2 text-sm">
                    <input name="usage_limit" type="number" min="1" value="{{ $coupon->usage_limit }}" class="rounded border border-gray-200 px-3 py-2 text-sm">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Active</label>
                </div>
                <button type="submit" class="mt-3 rounded bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-900">Update</button>
            </form>
        @endforeach
    </div>

    <div class="mt-4">{{ $coupons->links() }}</div>
@endsection
