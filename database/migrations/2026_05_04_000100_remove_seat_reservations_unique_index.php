<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seat_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('seat_reservations', 'seat_number')) {
                $table->dropUnique(['college', 'seat_number']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('seat_reservations', function (Blueprint $table) {
            $table->unique(['college', 'seat_number']);
        });
    }
};
