<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255);
            $table->string('related_transaction_type')->comment('WalletLoadLog, CoinTransfer, PaymentReceipt, etc.');
            $table->unsignedBigInteger('related_transaction_id')->nullable();
            $table->timestamp('refunded_at')->useCurrent();
            $table->timestamps();

            $table->index(['staff_user_id', 'created_at']);
            $table->index(['student_user_id', 'created_at']);
            $table->index(['related_transaction_type', 'related_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
