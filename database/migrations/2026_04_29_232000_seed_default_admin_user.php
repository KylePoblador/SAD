<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('users')) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@coinmeal.local'],
            [
                'name' => 'System Admin',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where('email', 'admin@coinmeal.local')
            ->delete();
    }
};
