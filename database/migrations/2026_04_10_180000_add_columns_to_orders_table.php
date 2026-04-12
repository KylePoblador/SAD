<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->nullable();
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['pending', 'preparing', 'ready', 'completed'])->default('pending');
            }
            if (!Schema::hasColumn('orders', 'total')) {
                $table->decimal('total', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('orders', 'canteen_id')) {
                $table->string('canteen_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'canteen_id')) {
                $table->dropColumn('canteen_id');
            }
            if (Schema::hasColumn('orders', 'total')) {
                $table->dropColumn('total');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'order_number')) {
                $table->dropColumn('order_number');
            }
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
