<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canteen_feedbacks', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->text('staff_reply')->nullable()->after('comment');
            $table->timestamp('staff_reply_at')->nullable()->after('staff_reply');
            $table->foreignId('staff_reply_user_id')->nullable()->after('staff_reply_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('canteen_feedbacks', function (Blueprint $table) {
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('canteen_feedbacks', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
            $table->dropForeign(['order_id']);
            $table->dropForeign(['staff_reply_user_id']);
            $table->dropColumn(['order_id', 'staff_reply', 'staff_reply_at', 'staff_reply_user_id']);
        });
    }
};
