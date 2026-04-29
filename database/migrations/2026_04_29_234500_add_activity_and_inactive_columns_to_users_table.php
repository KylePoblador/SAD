<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'is_inactive')) {
                $table->boolean('is_inactive')->default(false)->after('last_login_at');
            }
            if (! Schema::hasColumn('users', 'inactive_labeled_at')) {
                $table->timestamp('inactive_labeled_at')->nullable()->after('is_inactive');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'inactive_labeled_at')) {
                $table->dropColumn('inactive_labeled_at');
            }
            if (Schema::hasColumn('users', 'is_inactive')) {
                $table->dropColumn('is_inactive');
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};
