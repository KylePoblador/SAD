<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seat_reservations', function (Blueprint $table) {
            $table->string('reservation_code', 10)->nullable()->unique()->after('id');
            $table->foreignId('host_user_id')->nullable()->after('reservation_code')->constrained('users')->nullOnDelete();
        });

        DB::table('seat_reservations')
            ->select('id', 'user_id', 'host_user_id', 'reservation_code')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $code = $row->reservation_code ?: strtoupper(Str::random(8));
                    DB::table('seat_reservations')
                        ->where('id', $row->id)
                        ->update([
                            'reservation_code' => $code,
                            'host_user_id' => $row->host_user_id ?? $row->user_id,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('host_user_id');
            $table->dropColumn('reservation_code');
        });
    }
};
