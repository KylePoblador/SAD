<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('password_reset_pins');
    }

    public function down(): void
    {
        // Table intentionally not recreated; PIN reset flow was removed.
    }
};
