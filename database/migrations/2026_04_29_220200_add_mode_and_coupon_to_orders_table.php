<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_mode')) {
                $table->enum('order_mode', ['dine_in', 'takeout'])->default('dine_in')->after('status');
            }
            if (! Schema::hasColumn('orders', 'seat_number')) {
                $table->unsignedTinyInteger('seat_number')->nullable()->after('order_mode');
            }
            if (! Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('canteen_id')->constrained('coupons')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('orders', 'payable_total')) {
                $table->decimal('payable_total', 10, 2)->nullable()->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropForeign(['coupon_id']);
                $table->dropColumn('coupon_id');
            }
            if (Schema::hasColumn('orders', 'payable_total')) {
                $table->dropColumn('payable_total');
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
            if (Schema::hasColumn('orders', 'seat_number')) {
                $table->dropColumn('seat_number');
            }
            if (Schema::hasColumn('orders', 'order_mode')) {
                $table->dropColumn('order_mode');
            }
        });
    }
};
