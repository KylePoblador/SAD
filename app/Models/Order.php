<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total',
        'canteen_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
}

