<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin coupons</title>
    @include('partials.coinmeal-assets')
</head>
<body class="bg-gray-50 text-gray-900">
<div class="mx-auto max-w-6xl p-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Coupons</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 underline">Back to dashboard</a>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

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
            <button type="submit" class="rounded bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">Save coupon</button>
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
                <button type="submit" class="mt-3 rounded bg-gray-800 px-3 py-2 text-sm font-semibold text-white">Update</button>
            </form>
        @endforeach
    </div>
</div>
</body>
</html>
