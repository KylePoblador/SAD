<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('college');
            $table->unsignedTinyInteger('seat_number');
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->timestamps();

            $table->unique(['college', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_layouts');
    }
};
