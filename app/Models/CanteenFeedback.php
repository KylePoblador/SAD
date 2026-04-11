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
        'college',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function averageRatingForCollege(string $college): string
    {
        $avg = static::query()->where('college', $college)->avg('rating');

        return $avg !== null ? number_format(round((float) $avg, 1), 1) : '0.0';
    }
}
