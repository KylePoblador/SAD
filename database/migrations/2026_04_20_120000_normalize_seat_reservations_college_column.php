<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('seat_reservations')) {
            return;
        }

        try {
            DB::statement('UPDATE seat_reservations SET college = LOWER(TRIM(college))');
        } catch (Throwable) {
            // If duplicates appear after normalization, leave rows as-is; app queries use LOWER(TRIM(...)).
        }
    }

    public function down(): void
    {
        //
    }
};
