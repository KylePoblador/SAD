<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
    }

    public function down(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('type', 50);
            $table->string('college', 64)->nullable();
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->decimal('percent_discount', 5, 2)->nullable();
            $table->unsignedInteger('plan_days')->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redeemed_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('college', 64);
            $table->decimal('credited_amount', 10, 2)->default(0);
            $table->decimal('reference_amount', 10, 2)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();
        });
    }
};
