<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityNotification extends Model
{
    public const TYPE_DEPOSIT_INQUIRY_STUDENT = 'deposit_inquiry_student';

    public const TYPE_DEPOSIT_INQUIRY_STAFF = 'deposit_inquiry_staff';

    public const TYPE_DEPOSIT_INQUIRY_DONE = 'deposit_inquiry_done';

    public const TYPE_WALLET_LOADED = 'wallet_loaded';

    public const TYPE_SEAT_RESERVED = 'seat_reserved';

    public const TYPE_SEAT_RELEASED = 'seat_released';

    public const TYPE_FRIEND_REQUEST = 'friend_request';

    public const TYPE_FRIEND_ACCEPTED = 'friend_accepted';

    public const TYPE_WALLET_TRANSFER = 'wallet_transfer';

    public const TYPE_NEW_ORDER = 'new_order';

    public const TYPE_ORDER_CANCELLED = 'order_cancelled';

    public const TYPE_ORDER_STATUS = 'order_status';

    public const TYPE_REFUND_PENDING_STAFF = 'refund_pending_staff';

    public const TYPE_REFUND_DECISION = 'refund_decision';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'reference_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getBadgeStatusAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_WALLET_LOADED,
            self::TYPE_DEPOSIT_INQUIRY_DONE,
            self::TYPE_SEAT_RESERVED,
            self::TYPE_FRIEND_ACCEPTED,
            self::TYPE_WALLET_TRANSFER,
            self::TYPE_ORDER_STATUS => 'completed',
            self::TYPE_NEW_ORDER => 'preparing',
            self::TYPE_ORDER_CANCELLED => 'ready',
            self::TYPE_SEAT_RELEASED => 'ready',
            self::TYPE_DEPOSIT_INQUIRY_STUDENT,
            self::TYPE_FRIEND_REQUEST => 'preparing',
            self::TYPE_DEPOSIT_INQUIRY_STAFF,
            self::TYPE_REFUND_PENDING_STAFF => 'pending',
            self::TYPE_REFUND_DECISION => 'completed',
            default => 'info',
        };
    }

    public function getIconKeyAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_WALLET_LOADED,
            self::TYPE_DEPOSIT_INQUIRY_STUDENT,
            self::TYPE_DEPOSIT_INQUIRY_STAFF,
            self::TYPE_WALLET_TRANSFER,
            self::TYPE_DEPOSIT_INQUIRY_DONE => 'wallet',
            self::TYPE_SEAT_RESERVED,
            self::TYPE_SEAT_RELEASED => 'seat',
            self::TYPE_FRIEND_REQUEST,
            self::TYPE_FRIEND_ACCEPTED => 'users',
            self::TYPE_NEW_ORDER,
            self::TYPE_ORDER_CANCELLED,
            self::TYPE_ORDER_STATUS,
            self::TYPE_REFUND_PENDING_STAFF,
            self::TYPE_REFUND_DECISION => 'bell',
            default => 'bell',
        };
    }

    public static function notifyUser(int $userId, string $type, string $title, ?string $body = null, ?int $referenceId = null): self
    {
        return self::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * @param  callable(User): void  $callback  optional hook per staff (e.g. push)
     */
    public static function notifyStaffOfCollege(string $college, string $type, string $title, ?string $body = null, ?int $referenceId = null): void
    {
        $slug = strtolower(trim($college));

        User::query()
            ->where('role', 'staff')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$slug])
            ->each(function (User $staff) use ($type, $title, $body, $referenceId) {
                self::notifyUser($staff->id, $type, $title, $body, $referenceId);
            });
    }
}
