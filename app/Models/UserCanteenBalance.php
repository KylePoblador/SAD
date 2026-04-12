<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class UserCanteenBalance extends Model
{
    protected $fillable = [
        'user_id',
        'college',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function normalizedCollege(string $college): string
    {
        return strtolower(trim($college));
    }

    public static function balanceFor(int $userId, string $college): float
    {
        $college = self::normalizedCollege($college);

        $v = static::query()
            ->where('user_id', $userId)
            ->where('college', $college)
            ->value('balance');

        return round((float) ($v ?? 0), 2);
    }

    /**
     * Add amount to the student’s balance for one canteen slug; returns new balance for that canteen.
     */
    public static function add(int $userId, string $college, float $amount): float
    {
        $college = self::normalizedCollege($college);

        return (float) DB::transaction(function () use ($userId, $college, $amount) {
            $row = static::query()->firstOrCreate(
                ['user_id' => $userId, 'college' => $college],
                ['balance' => 0]
            );

            $row->balance = round((float) $row->balance + $amount, 2);
            $row->save();

            $total = round((float) static::query()->where('user_id', $userId)->sum('balance'), 2);
            User::query()->whereKey($userId)->update(['wallet_balance' => $total]);

            return (float) $row->balance;
        });
    }

    /**
     * Deduct amount from the student’s balance for one canteen slug; returns new balance for that canteen.
     *
     * @throws \RuntimeException If the row is missing or balance is insufficient.
     */
    public static function subtract(int $userId, string $college, float $amount): float
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Subtract amount must be positive.');
        }

        $college = self::normalizedCollege($college);

        return (float) DB::transaction(function () use ($userId, $college, $amount) {
            $row = static::query()
                ->where('user_id', $userId)
                ->where('college', $college)
                ->lockForUpdate()
                ->first();

            if (! $row || (float) $row->balance + 1e-6 < $amount) {
                throw new \RuntimeException('Insufficient balance for this canteen.');
            }

            $row->balance = round((float) $row->balance - $amount, 2);
            $row->save();

            $total = round((float) static::query()->where('user_id', $userId)->sum('balance'), 2);
            User::query()->whereKey($userId)->update(['wallet_balance' => $total]);

            return (float) $row->balance;
        });
    }

    public static function totalForUser(int $userId): float
    {
        return round((float) static::query()->where('user_id', $userId)->sum('balance'), 2);
    }
}
