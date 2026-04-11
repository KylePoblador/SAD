<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletDepositInquiry extends Model
{
    protected $fillable = [
        'user_id',
        'college',
        'intended_amount',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'intended_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
