@php use App\Services\AdminTransactionService; @endphp
<div class="overflow-x-auto">
    <table class="w-full min-w-[640px] text-left text-sm">
        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-3 py-2">Date</th>
                <th class="px-3 py-2">Type</th>
                <th class="px-3 py-2">User</th>
                <th class="px-3 py-2">Counterparty</th>
                <th class="px-3 py-2">Description</th>
                <th class="px-3 py-2 text-right">Amount</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($transactions as $tx)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ AdminTransactionService::formatAt($tx->occurred_at) }}</td>
                    <td class="px-3 py-2">
                        <span class="rounded px-2 py-0.5 text-xs font-semibold {{ AdminTransactionService::typeBadgeClass($tx->type) }}">
                            {{ $tx->type_label }}
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        @if($tx->user)
                            <a href="{{ route('admin.users.show', $tx->user->id) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $tx->user->name }}
                            </a>
                            <p class="text-xs text-gray-500">{{ $tx->user->email }} · {{ ucfirst($tx->user->role) }}</p>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($tx->counterparty)
                            <a href="{{ route('admin.users.show', $tx->counterparty->id) }}" class="text-indigo-600 hover:underline">{{ $tx->counterparty->name }}</a>
                            <p class="text-xs text-gray-500">{{ ucfirst($tx->counterparty->role) }}</p>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="max-w-xs px-3 py-2 text-gray-700">{{ $tx->description }}</td>
                    <td class="whitespace-nowrap px-3 py-2 text-right font-semibold">₱{{ number_format($tx->amount, 2) }}</td>
                    <td class="px-3 py-2 capitalize text-gray-600">{{ $tx->status ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-gray-500">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
