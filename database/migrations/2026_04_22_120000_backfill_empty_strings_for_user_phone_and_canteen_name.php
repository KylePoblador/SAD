<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('phone')->update(['phone' => '']);
        DB::table('users')->whereNull('canteen_name')->update(['canteen_name' => '']);
    }

    public function down(): void
    {
        // No rollback needed for data backfill.
    }
};
