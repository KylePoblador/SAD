<?php

namespace App\Services;

use App\Models\ActivityNotification;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Models\UserCanteenBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderCancellationService
{
    /** Statuses where a student may still cancel (in progress, not completed). */
    public const CANCELLABLE_STATUSES = ['pending', 'preparing', 'ready'];

    /**
     * Request cancellation: order is marked cancelled and a pending refund is queued for staff.
     */
    public function cancelByStudent(Order $order, User $student): Order
    {
        if ((int) $order->user_id !== (int) $student->id) {
            throw new \InvalidArgumentException('You can only cancel your own orders.');
        }

        if (! in_array($order->status, self::CANCELLABLE_STATUSES, true)) {
            throw new \InvalidArgumentException('Only in-process orders can be cancelled. Completed orders cannot be cancelled.');
        }

        return DB::transaction(function () use ($order, $student) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, self::CANCELLABLE_STATUSES, true)) {
                throw new \InvalidArgumentException('This order can no longer be cancelled.');
            }

            if (Schema::hasTable('refunds') && Schema::hasColumn('refunds', 'order_id')) {
                $existing = Refund::query()
                    ->where('order_id', $locked->id)
                    ->where('status', Refund::STATUS_PENDING)
                    ->exists();
                if ($existing) {
                    throw new \InvalidArgumentException('A refund request for this order is already pending.');
                }
            }

            $college = UserCanteenBalance::normalizedCollege((string) $locked->canteen_id);
            $payable = round((float) ($locked->payable_total ?? $locked->total ?? 0), 2);

            if ($locked->coupon_id && Schema::hasTable('coupons')) {
                Coupon::query()
                    ->whereKey($locked->coupon_id)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            if (Schema::hasTable('seat_reservations')) {
                DB::table('seat_reservations')
                    ->where('user_id', $student->id)
                    ->whereRaw('LOWER(TRIM(college)) = ?', [$college])
                    ->delete();
            }

            $locked->update(['status' => 'cancelled']);

            if ($payable > 0 && Schema::hasTable('refunds')) {
                $refundPayload = [
                    'student_user_id' => $student->id,
                    'amount' => $payable,
                    'reason' => 'Student cancelled order '.($locked->order_number ?? '#'.$locked->id),
                    'related_transaction_type' => 'Order',
                    'related_transaction_id' => $locked->id,
                    'refunded_at' => null,
                ];

                if (Schema::hasColumn('refunds', 'canteen_id')) {
                    $refundPayload['canteen_id'] = $college;
                }
                if (Schema::hasColumn('refunds', 'order_id')) {
                    $refundPayload['order_id'] = $locked->id;
                }
                if (Schema::hasColumn('refunds', 'status')) {
                    $refundPayload['status'] = Refund::STATUS_PENDING;
                    $refundPayload['staff_user_id'] = null;
                } else {
                    $refundPayload['staff_user_id'] = $student->id;
                }

                Refund::create($refundPayload);
            }

            $orderLabel = $locked->order_number ?? 'Order #'.$locked->id;

            ActivityNotification::notifyUser(
                (int) $student->id,
                ActivityNotification::TYPE_ORDER_CANCELLED,
                'Cancellation submitted',
                $orderLabel.' was cancelled. Staff will review your refund request (₱'.number_format($payable, 2).').',
                $locked->id
            );

            ActivityNotification::notifyStaffOfCollege(
                $college,
                ActivityNotification::TYPE_REFUND_PENDING_STAFF,
                'Refund review needed',
                $student->name.' cancelled '.$orderLabel.' (₱'.number_format($payable, 2).'). Approve or reject in Refund Management.',
                $locked->id
            );

            return $locked->fresh();
        });
    }
}
