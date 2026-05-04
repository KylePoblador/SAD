<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connection_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['requester_user_id', 'receiver_user_id'], 'connection_requests_unique_pair');
            $table->index(['receiver_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connection_requests');
    }
};
