<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('college', 64);
            $table->decimal('amount', 10, 2);
            $table->string('token', 100)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('scanned_at')->nullable();
            $table->foreignId('scanned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'processed', 'expired', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_qr_sessions');
    }
};
