<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_deposit_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('college', 32);
            $table->decimal('intended_amount', 10, 2)->nullable();
            $table->string('note', 500)->nullable();
            $table->string('status', 24)->default('pending');
            $table->timestamps();

            $table->index(['college', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_deposit_inquiries');
    }
};
