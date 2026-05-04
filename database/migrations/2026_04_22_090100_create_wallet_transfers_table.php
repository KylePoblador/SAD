<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('college', 64);
            $table->decimal('amount', 10, 2);
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transfers');
    }
};
