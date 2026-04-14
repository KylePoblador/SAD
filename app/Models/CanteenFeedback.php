<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores per-canteen ratings/comments. Average rating for a college slug is used in staff/student UIs.
 * Insert rows when you implement “rate order” or feedback forms (e.g. after completed orders).
 */
class CanteenFeedback extends Model
{
    protected $table = 'canteen_feedbacks';

    protected $fillable = [
        'user_id',
        'order_id',
        'college',
        'rating',
        'comment',
        'staff_reply',
        'staff_reply_at',
        'staff_reply_user_id',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'staff_reply_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function staffReplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_reply_user_id');
    }

    public static function averageRatingForCollege(string $college): string
    {
        $avg = static::query()->where('college', $college)->avg('rating');

        return $avg !== null ? number_format(round((float) $avg, 1), 1) : '0.0';
    }
}
