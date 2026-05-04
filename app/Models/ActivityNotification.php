<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityNotification extends Model
{
    public const TYPE_WALLET_LOADED = 'wallet_loaded';

    public const TYPE_SEAT_RESERVED = 'seat_reserved';

    public const TYPE_SEAT_RELEASED = 'seat_released';

    public const TYPE_WALLET_TRANSFER_SENT = 'wallet_transfer_sent';

    public const TYPE_WALLET_TRANSFER_RECEIVED = 'wallet_transfer_received';

    public const TYPE_QR_PAYMENT_PROCESSED = 'qr_payment_processed';
    public const TYPE_ORDER_PLACED = 'order_placed';
    public const TYPE_ORDER_STATUS_UPDATED = 'order_status_updated';
    public const TYPE_FEEDBACK_RECEIVED = 'feedback_received';
    public const TYPE_FEEDBACK_REPLIED = 'feedback_replied';
    public const TYPE_WALLET_LOAD_PROCESSED = 'wallet_load_processed';
    public const TYPE_QR_SCAN_PAYMENT_PROCESSED = 'qr_scan_payment_processed';

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
            self::TYPE_SEAT_RESERVED => 'completed',
            self::TYPE_SEAT_RELEASED => 'ready',
            self::TYPE_WALLET_TRANSFER_SENT,
            self::TYPE_WALLET_TRANSFER_RECEIVED,
            self::TYPE_QR_PAYMENT_PROCESSED,
            self::TYPE_ORDER_PLACED,
            self::TYPE_ORDER_STATUS_UPDATED,
            self::TYPE_FEEDBACK_RECEIVED,
            self::TYPE_FEEDBACK_REPLIED,
            self::TYPE_WALLET_LOAD_PROCESSED,
            self::TYPE_QR_SCAN_PAYMENT_PROCESSED => 'completed',
            default => 'info',
        };
    }

    public function getIconKeyAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_WALLET_LOADED => 'wallet',
            self::TYPE_SEAT_RESERVED,
            self::TYPE_SEAT_RELEASED => 'seat',
            self::TYPE_WALLET_TRANSFER_SENT,
            self::TYPE_WALLET_TRANSFER_RECEIVED,
            self::TYPE_QR_PAYMENT_PROCESSED,
            self::TYPE_WALLET_LOAD_PROCESSED,
            self::TYPE_QR_SCAN_PAYMENT_PROCESSED => 'wallet',
            self::TYPE_ORDER_PLACED,
            self::TYPE_ORDER_STATUS_UPDATED,
            self::TYPE_FEEDBACK_RECEIVED,
            self::TYPE_FEEDBACK_REPLIED => 'order',
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
