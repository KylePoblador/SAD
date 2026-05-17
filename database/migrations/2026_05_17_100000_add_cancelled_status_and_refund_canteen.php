<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'status')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY status VARCHAR(32) NOT NULL DEFAULT 'pending'");
            } else {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('status', 32)->default('pending')->change();
                });
            }
        }

        if (Schema::hasTable('refunds') && ! Schema::hasColumn('refunds', 'canteen_id')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->string('canteen_id', 64)->nullable()->after('student_user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('refunds') && Schema::hasColumn('refunds', 'canteen_id')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->dropColumn('canteen_id');
            });
        }
    }
};
