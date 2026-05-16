@extends('admin.layout')

@section('title', 'Canteen Reports')
@section('heading', 'Canteen Reports')

@section('content')
    <div class="grid grid-cols-1 gap-6">
        @forelse($canteenReports as $report)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold uppercase tracking-wide text-indigo-700">Canteen: {{ $report->canteen_id }}</h2>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-gray-50 p-4 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500">Daily Sales</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">₱{{ number_format($report->daily_sales, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500">Weekly Sales</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">₱{{ number_format($report->weekly_sales, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500">Monthly Sales</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">₱{{ number_format($report->monthly_sales, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-indigo-50 p-4 border border-indigo-100">
                        <p class="text-sm font-medium text-indigo-700">Total Sales</p>
                        <p class="mt-1 text-2xl font-bold text-indigo-900">₱{{ number_format($report->total_sales, 2) }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-white p-8 text-center shadow-sm">
                <p class="text-gray-500">No canteen reports available yet.</p>
            </div>
        @endforelse
    </div>
@endsection
