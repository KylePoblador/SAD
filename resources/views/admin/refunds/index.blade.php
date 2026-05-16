@extends('admin.layout')

@section('title', 'Refunds')
@section('heading', 'Refunds')

@section('content')
    <div class="mb-4 rounded-xl bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-600">Total refunded: <span class="text-lg font-bold text-gray-900">₱{{ number_format($totalRefunded, 2) }}</span></p>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Staff</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Reason</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($refunds as $refund)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ ($refund->refunded_at ?? $refund->created_at)?->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if($refund->student)
                                <a href="{{ route('admin.users.show', $refund->student) }}" class="font-medium text-indigo-600 hover:underline">{{ $refund->student->name }}</a>
                            @else — @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($refund->staff)
                                <a href="{{ route('admin.users.show', $refund->staff) }}" class="text-indigo-600 hover:underline">{{ $refund->staff->name }}</a>
                            @else — @endif
                        </td>
                        <td class="px-4 py-3 font-semibold">₱{{ number_format((float) $refund->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $refund->reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No refunds yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-gray-100 p-4">{{ $refunds->links() }}</div>
    </div>
@endsection
