<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletLoadLog extends Model
{
    protected $fillable = [
        'student_user_id',
        'staff_user_id',
        'college',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }
}
