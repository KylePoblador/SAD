<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class SeatLayout extends Model
{
    protected $table = 'seat_layouts';

    protected $fillable = [
        'college',
        'seat_number',
        'capacity',
    ];

    public static function getLayoutForCollege(string $college, int $seatCount = 25): Collection
    {
        if (! Schema::hasTable('seat_layouts')) {
            return collect(range(1, $seatCount))->mapWithKeys(fn ($seatNumber) => [
                $seatNumber => 1,
            ]);
        }

        $college = strtolower(trim($college));

        $rows = self::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$college])
            ->get()
            ->keyBy('seat_number')
            ->map(fn ($row) => (int) $row->capacity);

        return collect(range(1, $seatCount))->mapWithKeys(fn ($seatNumber) => [
            $seatNumber => $rows[$seatNumber] ?? 1,
        ]);
    }
}
