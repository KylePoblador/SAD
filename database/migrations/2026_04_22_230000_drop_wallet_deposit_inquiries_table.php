<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('wallet_deposit_inquiries');
    }

    public function down(): void
    {
        Schema::create('wallet_deposit_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('college', 64);
            $table->decimal('intended_amount', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->timestamps();
        });
    }
};
