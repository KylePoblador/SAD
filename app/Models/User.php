<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'college',
        'wallet_balance',
        'avatar_path',
        'phone',
        'student_id',
        'canteen_name',
        'last_login_at',
        'is_inactive',
        'inactive_labeled_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_feed_cleared_at' => 'datetime',
            'last_login_at' => 'datetime',
            'inactive_labeled_at' => 'datetime',
            'is_inactive' => 'boolean',
        ];
    }

    public function avatarPublicUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }
        if (Storage::disk('public')->exists($this->avatar_path)) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return asset($this->avatar_path);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function walletLoadsAsStudent()
    {
        return $this->hasMany(WalletLoadLog::class, 'student_user_id');
    }

    public function walletLoadsAsStaff()
    {
        return $this->hasMany(WalletLoadLog::class, 'staff_user_id');
    }

    public function coinTransfersSent()
    {
        return $this->hasMany(CoinTransfer::class, 'sender_user_id');
    }

    public function coinTransfersReceived()
    {
        return $this->hasMany(CoinTransfer::class, 'receiver_user_id');
    }

    public function refundsAsStudent()
    {
        return $this->hasMany(Refund::class, 'student_user_id');
    }

    public function refundsAsStaff()
    {
        return $this->hasMany(Refund::class, 'staff_user_id');
    }

    public function friendshipsSent()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function friendshipsReceived()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    public function getFriendsAttribute()
    {
        $sent = $this->friendshipsSent()->where('status', 'accepted')->with('friend')->get()->pluck('friend');
        $received = $this->friendshipsReceived()->where('status', 'accepted')->with('user')->get()->pluck('user');

        return $sent->merge($received);
    }
}
