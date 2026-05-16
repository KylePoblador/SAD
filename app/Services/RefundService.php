<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RefundService
{
    /**
     * Process a refund for a student
     */
    public function issueRefund(
        User $staff,
        User $student,
        float $amount,
        string $reason,
        ?string $transactionType = null,
        ?int $transactionId = null
    ): Refund {
        if ($staff->role !== 'staff') {
            throw new \InvalidArgumentException('Only staff members can issue refunds');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be greater than 0');
        }

        return DB::transaction(function () use ($staff, $student, $amount, $reason, $transactionType, $transactionId) {
            // Create refund record
            $refund = Refund::create([
                'staff_user_id' => $staff->id,
                'student_user_id' => $student->id,
                'amount' => $amount,
                'reason' => $reason,
                'related_transaction_type' => $transactionType,
                'related_transaction_id' => $transactionId,
                'refunded_at' => now(),
            ]);

            // Add refund amount back to student's wallet
            $student->increment('wallet_balance', $amount);

            return $refund;
        });
    }

    /**
     * Get refund history for a student
     */
    public function getStudentRefundHistory(User $student, int $limit = 50)
    {
        return Refund::where('student_user_id', $student->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get refunds issued by a staff member
     */
    public function getStaffRefundHistory(User $staff, int $limit = 50)
    {
        return Refund::where('staff_user_id', $staff->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total refunds by staff (for auditing)
     */
    public function getTotalRefundsByStaff(User $staff)
    {
        return Refund::where('staff_user_id', $staff->id)
            ->sum('amount');
    }
}
