<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('refunds')) {
            return;
        }

        Schema::table('refunds', function (Blueprint $table) {
            if (! Schema::hasColumn('refunds', 'status')) {
                $table->string('status', 20)->default('refunded')->after('amount');
            }
            if (! Schema::hasColumn('refunds', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('canteen_id')->constrained('orders')->nullOnDelete();
            }
            if (! Schema::hasColumn('refunds', 'processed_by_staff_user_id')) {
                $table->foreignId('processed_by_staff_user_id')->nullable()->after('staff_user_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('refunds', 'staff_notes')) {
                $table->string('staff_notes', 500)->nullable()->after('reason');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE refunds MODIFY staff_user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE refunds MODIFY refunded_at TIMESTAMP NULL');
        }

        DB::table('refunds')->whereNull('status')->update(['status' => 'refunded']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('refunds')) {
            return;
        }

        Schema::table('refunds', function (Blueprint $table) {
            if (Schema::hasColumn('refunds', 'staff_notes')) {
                $table->dropColumn('staff_notes');
            }
            if (Schema::hasColumn('refunds', 'processed_by_staff_user_id')) {
                $table->dropConstrainedForeignId('processed_by_staff_user_id');
            }
            if (Schema::hasColumn('refunds', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }
            if (Schema::hasColumn('refunds', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
