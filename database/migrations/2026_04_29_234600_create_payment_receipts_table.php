<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('qr_payment_token_id')->nullable()->constrained('qr_payment_tokens')->nullOnDelete();
            $table->string('receipt_number', 40)->unique();
            $table->string('canteen_id', 40)->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at');
            $table->timestamps();
            $table->index(['user_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
