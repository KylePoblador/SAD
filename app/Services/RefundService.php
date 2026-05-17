<?php

namespace App\Services;

use App\Models\ActivityNotification;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Models\UserCanteenBalance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefundService
{
    /**
     * Process a manual refund for a student (immediate, no pending review).
     */
    public function issueRefund(
        User $staff,
        User $student,
        float $amount,
        string $reason,
        ?string $transactionType = null,
        ?int $transactionId = null,
        ?string $canteenId = null
    ): Refund {
        if ($staff->role !== 'staff') {
            throw new \InvalidArgumentException('Only staff members can issue refunds');
        }

        if ($student->role !== 'student') {
            throw new \InvalidArgumentException('Refunds can only be issued to students');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be greater than 0');
        }

        $college = $this->resolveCanteenId($canteenId, $transactionType, $transactionId, $student);

        return DB::transaction(function () use ($staff, $student, $amount, $reason, $transactionType, $transactionId, $college) {
            $payload = [
                'staff_user_id' => $staff->id,
                'processed_by_staff_user_id' => $staff->id,
                'student_user_id' => $student->id,
                'amount' => $amount,
                'reason' => $reason,
                'related_transaction_type' => $transactionType ?? 'manual',
                'related_transaction_id' => $transactionId,
                'refunded_at' => now(),
            ];

            if (Schema::hasColumn('refunds', 'canteen_id')) {
                $payload['canteen_id'] = $college;
            }
            if (Schema::hasColumn('refunds', 'status')) {
                $payload['status'] = Refund::STATUS_REFUNDED;
            }

            $refund = Refund::create($payload);

            UserCanteenBalance::add((int) $student->id, $college, $amount);

            ActivityNotification::notifyUser(
                (int) $student->id,
                ActivityNotification::TYPE_REFUND_DECISION,
                'Refund received',
                '₱'.number_format($amount, 2).' was refunded by '.$staff->name.'. '.$reason,
                $refund->id
            );

            return $refund;
        });
    }

    /**
     * Staff approves or rejects a pending cancellation refund.
     */
    public function processPendingRefund(Refund $refund, User $staff, string $decision, ?string $staffNotes = null): Refund
    {
        if ($staff->role !== 'staff') {
            throw new \InvalidArgumentException('Only staff can process refund requests.');
        }

        if ($refund->status !== Refund::STATUS_PENDING) {
            throw new \InvalidArgumentException('This refund request has already been processed.');
        }

        $staffCollege = UserCanteenBalance::normalizedCollege((string) ($staff->college ?? ''));
        $refundCollege = UserCanteenBalance::normalizedCollege((string) ($refund->canteen_id ?? ''));
        if ($staffCollege === '' || $staffCollege !== $refundCollege) {
            throw new \InvalidArgumentException('You can only process refunds for your assigned canteen.');
        }

        if (! in_array($decision, [Refund::STATUS_REFUNDED, Refund::STATUS_REJECTED], true)) {
            throw new \InvalidArgumentException('Invalid refund decision.');
        }

        if ($decision === Refund::STATUS_REJECTED && ! trim((string) $staffNotes)) {
            throw new \InvalidArgumentException('Please provide a reason when rejecting a refund.');
        }

        return DB::transaction(function () use ($refund, $staff, $decision, $staffNotes) {
            $locked = Refund::query()->whereKey($refund->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Refund::STATUS_PENDING) {
                throw new \InvalidArgumentException('This refund request has already been processed.');
            }

            $student = User::query()->findOrFail($locked->student_user_id);
            $college = UserCanteenBalance::normalizedCollege((string) $locked->canteen_id);
            $amount = round((float) $locked->amount, 2);
            $orderLabel = $locked->order?->order_number ?? ('Order #'.($locked->order_id ?? $locked->related_transaction_id));

            if ($decision === Refund::STATUS_REFUNDED) {
                if ($amount > 0) {
                    UserCanteenBalance::add((int) $student->id, $college, $amount);
                }

                $locked->update([
                    'status' => Refund::STATUS_REFUNDED,
                    'staff_user_id' => $staff->id,
                    'processed_by_staff_user_id' => $staff->id,
                    'staff_notes' => $staffNotes,
                    'refunded_at' => now(),
                ]);

                ActivityNotification::notifyUser(
                    (int) $student->id,
                    ActivityNotification::TYPE_REFUND_DECISION,
                    'Refund approved',
                    'Your cancellation for '.$orderLabel.' was approved. ₱'.number_format($amount, 2).' was returned to your wallet.',
                    $locked->id
                );
            } else {
                $locked->update([
                    'status' => Refund::STATUS_REJECTED,
                    'processed_by_staff_user_id' => $staff->id,
                    'staff_notes' => $staffNotes,
                    'refunded_at' => null,
                ]);

                ActivityNotification::notifyUser(
                    (int) $student->id,
                    ActivityNotification::TYPE_REFUND_DECISION,
                    'Refund rejected',
                    'Your refund request for '.$orderLabel.' was rejected. '.$staffNotes,
                    $locked->id
                );
            }

            return $locked->fresh(['student:id,name', 'order:id,order_number']);
        });
    }

    private function resolveCanteenId(
        ?string $canteenId,
        ?string $transactionType,
        ?int $transactionId,
        User $student
    ): string {
        if ($canteenId) {
            $normalized = UserCanteenBalance::normalizedCollege($canteenId);
            if (! isset(config('canteens', [])[$normalized])) {
                throw new \InvalidArgumentException('Invalid canteen selected for refund.');
            }

            return $normalized;
        }

        if ($transactionType === 'Order' && $transactionId) {
            $order = Order::query()->find($transactionId);
            if ($order && (int) $order->user_id === (int) $student->id) {
                return UserCanteenBalance::normalizedCollege((string) $order->canteen_id);
            }
        }

        $fallback = UserCanteenBalance::normalizedCollege((string) ($student->college ?? ''));
        if ($fallback && isset(config('canteens', [])[$fallback])) {
            return $fallback;
        }

        throw new \InvalidArgumentException('Select a canteen for this refund, or link it to an order.');
    }

    public function getStudentRefundHistory(User $student, int $limit = 50)
    {
        return Refund::where('student_user_id', $student->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getRefundsForStaffCanteen(User $staff, int $limit = 100): Collection
    {
        $college = UserCanteenBalance::normalizedCollege((string) ($staff->college ?? ''));
        if ($college === '') {
            return collect();
        }

        return Refund::query()
            ->with([
                'student:id,name,email,student_id',
                'order:id,order_number,status,created_at',
                'processedBy:id,name',
            ])
            ->where('canteen_id', $college)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function countPendingForStaffCanteen(User $staff): int
    {
        $college = UserCanteenBalance::normalizedCollege((string) ($staff->college ?? ''));

        return (int) Refund::query()
            ->where('canteen_id', $college)
            ->where('status', Refund::STATUS_PENDING)
            ->count();
    }

    public function getStaffRefundHistory(User $staff, int $limit = 50)
    {
        return $this->getRefundsForStaffCanteen($staff, $limit);
    }

    public function getTotalRefundsByStaff(User $staff)
    {
        $college = UserCanteenBalance::normalizedCollege((string) ($staff->college ?? ''));

        return Refund::query()
            ->where('canteen_id', $college)
            ->where('status', Refund::STATUS_REFUNDED)
            ->where(function ($q) use ($staff) {
                $q->where('processed_by_staff_user_id', $staff->id)
                    ->orWhere('staff_user_id', $staff->id);
            })
            ->sum('amount');
    }
}
