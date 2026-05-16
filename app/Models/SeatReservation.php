<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatReservation extends Model
{
    protected $fillable = [
        'user_id',
        'college',
        'seat_number',
        'share_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
