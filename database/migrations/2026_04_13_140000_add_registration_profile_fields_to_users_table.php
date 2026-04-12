<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 32)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'student_id')) {
                $table->string('student_id', 64)->nullable()->unique()->after('phone');
            }
            if (! Schema::hasColumn('users', 'canteen_name')) {
                $table->string('canteen_name', 255)->nullable()->after('student_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'canteen_name')) {
                $table->dropColumn('canteen_name');
            }
            if (Schema::hasColumn('users', 'student_id')) {
                $table->dropUnique(['student_id']);
                $table->dropColumn('student_id');
            }
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
