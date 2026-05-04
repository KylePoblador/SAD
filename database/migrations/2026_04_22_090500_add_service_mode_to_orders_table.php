<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'service_mode')) {
                $table->enum('service_mode', ['dine_in', 'takeout'])->default('dine_in')->after('canteen_id');
            }
            if (! Schema::hasColumn('orders', 'seat_number')) {
                $table->unsignedTinyInteger('seat_number')->nullable()->after('service_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'seat_number')) {
                $table->dropColumn('seat_number');
            }
            if (Schema::hasColumn('orders', 'service_mode')) {
                $table->dropColumn('service_mode');
            }
        });
    }
};
