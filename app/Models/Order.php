<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total',
        'canteen_id',
        'coupon_id',
        'order_mode',
        'seat_number',
        'discount_amount',
        'payable_total',
        'is_read',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'discount_amount' => 'decimal:2',
        'payable_total' => 'decimal:2',
    ];

    // Relationship to User (Student)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Order Items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(CanteenFeedback::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
