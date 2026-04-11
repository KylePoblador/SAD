<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'wallet_balance')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'wallet_balance')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 250');
        }
    }
};
