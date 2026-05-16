<?php

namespace App\Services;

use App\Models\CoinTransfer;
use App\Models\Order;
use App\Models\PaymentReceipt;
use App\Models\Refund;
use App\Models\User;
use App\Models\WalletLoadLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminTransactionService
{
    /** @return LengthAwarePaginator<object> */
    public function paginate(int $perPage = 30, ?string $type = null, ?string $role = null, ?int $userId = null): LengthAwarePaginator
    {
        $items = $this->collect($type, $role, $userId);
        $page = max(1, (int) request()->query('page', 1));
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator($slice, $items->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    /** @return Collection<int, object> */
    public function collect(?string $type = null, ?string $role = null, ?int $userId = null): Collection
    {
        $items = collect();

        if (! $type || $type === 'order') {
            $items = $items->merge($this->mapOrders($role, $userId));
        }
        if (! $type || $type === 'wallet_load') {
            $items = $items->merge($this->mapWalletLoads($role, $userId));
        }
        if (! $type || $type === 'coin_transfer') {
            $items = $items->merge($this->mapCoinTransfers($role, $userId));
        }
        if (! $type || $type === 'refund') {
            $items = $items->merge($this->mapRefunds($role, $userId));
        }
        if (! $type || $type === 'payment') {
            $items = $items->merge($this->mapPayments($role, $userId));
        }

        return $items->sortByDesc(fn ($row) => $row->occurred_at?->timestamp ?? 0)->values();
    }

    /** @return Collection<int, object> */
    public function recent(int $limit = 15, ?int $userId = null): Collection
    {
        return $this->collect(userId: $userId)->take($limit)->values();
    }

    public static function typeBadgeClass(string $type): string
    {
        return match ($type) {
            'order' => 'bg-blue-100 text-blue-800',
            'wallet_load' => 'bg-emerald-100 text-emerald-800',
            'coin_transfer' => 'bg-violet-100 text-violet-800',
            'refund' => 'bg-amber-100 text-amber-800',
            'payment' => 'bg-cyan-100 text-cyan-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function formatAt(?Carbon $at): string
    {
        return $at ? $at->format('M d, Y H:i') : '—';
    }

    /** @return Collection<int, object> */
    private function mapOrders(?string $role, ?int $userId): Collection
    {
        return Order::query()
            ->with('user:id,name,email,role,college')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($role, fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('role', $role)))
            ->whereHas('user', fn ($q) => $q->whereIn('role', ['student', 'staff']))
            ->latest()
            ->limit(400)
            ->get()
            ->map(fn (Order $order) => (object) [
                'key' => 'order-'.$order->id,
                'type' => 'order',
                'type_label' => 'Canteen order',
                'source_id' => $order->id,
                'occurred_at' => $order->created_at,
                'amount' => (float) ($order->payable_total ?? $order->total ?? 0),
                'status' => $order->status,
                'reference' => $order->order_number,
                'description' => 'Order #'.($order->order_number ?? $order->id),
                'user' => $order->user,
                'counterparty' => null,
            ]);
    }

    /** @return Collection<int, object> */
    private function mapWalletLoads(?string $role, ?int $userId): Collection
    {
        return WalletLoadLog::query()
            ->with(['student:id,name,email,role,college', 'staffMember:id,name,email,role,college'])
            ->when($userId, fn ($q) => $q->where(fn ($inner) => $inner
                ->where('student_user_id', $userId)->orWhere('staff_user_id', $userId)))
            ->when($role === 'student', fn ($q) => $q->whereHas('student', fn ($uq) => $uq->where('role', 'student')))
            ->when($role === 'staff', fn ($q) => $q->whereHas('staffMember', fn ($uq) => $uq->where('role', 'staff')))
            ->latest()
            ->limit(400)
            ->get()
            ->map(fn (WalletLoadLog $log) => (object) [
                'key' => 'wallet_load-'.$log->id,
                'type' => 'wallet_load',
                'type_label' => 'Wallet load',
                'source_id' => $log->id,
                'occurred_at' => $log->created_at,
                'amount' => (float) $log->amount,
                'status' => 'completed',
                'reference' => strtoupper($log->college),
                'description' => 'Wallet credited · '.strtoupper($log->college),
                'user' => $log->student,
                'counterparty' => $log->staffMember,
            ]);
    }

    /** @return Collection<int, object> */
    private function mapCoinTransfers(?string $role, ?int $userId): Collection
    {
        return CoinTransfer::query()
            ->with(['sender:id,name,email,role,college', 'receiver:id,name,email,role,college'])
            ->when($userId, fn ($q) => $q->where(fn ($inner) => $inner
                ->where('sender_user_id', $userId)->orWhere('receiver_user_id', $userId)))
            ->latest()
            ->limit(400)
            ->get()
            ->filter(fn (CoinTransfer $t) => in_array($t->sender?->role, ['student', 'staff'], true)
                || in_array($t->receiver?->role, ['student', 'staff'], true))
            ->map(fn (CoinTransfer $transfer) => (object) [
                'key' => 'coin_transfer-'.$transfer->id,
                'type' => 'coin_transfer',
                'type_label' => 'Coin transfer',
                'source_id' => $transfer->id,
                'occurred_at' => $transfer->created_at,
                'amount' => (float) $transfer->amount,
                'status' => 'completed',
                'reference' => strtoupper((string) $transfer->college),
                'description' => trim((string) ($transfer->note ?: 'Peer coin transfer')),
                'user' => $transfer->sender,
                'counterparty' => $transfer->receiver,
            ]);
    }

    /** @return Collection<int, object> */
    private function mapRefunds(?string $role, ?int $userId): Collection
    {
        return Refund::query()
            ->with(['student:id,name,email,role,college', 'staff:id,name,email,role,college'])
            ->when($userId, fn ($q) => $q->where(fn ($inner) => $inner
                ->where('student_user_id', $userId)->orWhere('staff_user_id', $userId)))
            ->when($role === 'student', fn ($q) => $q->whereHas('student', fn ($uq) => $uq->where('role', 'student')))
            ->when($role === 'staff', fn ($q) => $q->whereHas('staff', fn ($uq) => $uq->where('role', 'staff')))
            ->latest()
            ->limit(400)
            ->get()
            ->map(fn (Refund $refund) => (object) [
                'key' => 'refund-'.$refund->id,
                'type' => 'refund',
                'type_label' => 'Refund',
                'source_id' => $refund->id,
                'occurred_at' => $refund->refunded_at ?? $refund->created_at,
                'amount' => (float) $refund->amount,
                'status' => 'completed',
                'reference' => $refund->related_transaction_type,
                'description' => $refund->reason,
                'user' => $refund->student,
                'counterparty' => $refund->staff,
            ]);
    }

    /** @return Collection<int, object> */
    private function mapPayments(?string $role, ?int $userId): Collection
    {
        if (! Schema::hasTable('payment_receipts')) {
            return collect();
        }

        return PaymentReceipt::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($role, fn ($q) => $q->whereIn('user_id', User::query()->where('role', $role)->select('id')))
            ->latest()
            ->limit(400)
            ->get()
            ->map(function (PaymentReceipt $receipt) {
                $user = User::query()->select(['id', 'name', 'email', 'role', 'college'])->find($receipt->user_id);

                return (object) [
                    'key' => 'payment-'.$receipt->id,
                    'type' => 'payment',
                    'type_label' => 'Payment receipt',
                    'source_id' => $receipt->id,
                    'occurred_at' => $receipt->paid_at ?? $receipt->created_at,
                    'amount' => (float) $receipt->amount,
                    'status' => 'paid',
                    'reference' => $receipt->receipt_number,
                    'description' => 'Receipt #'.($receipt->receipt_number ?? $receipt->id),
                    'user' => $user,
                    'counterparty' => null,
                ];
            })
            ->filter(fn ($row) => $row->user && in_array($row->user->role, ['student', 'staff'], true));
    }
}
