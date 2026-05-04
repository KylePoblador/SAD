<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('role');
        });

        $inactiveThreshold = now()->subMonths(6);
        DB::table('users')
            ->whereIn('role', ['student', 'staff'])
            ->where('updated_at', '<', $inactiveThreshold)
            ->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
