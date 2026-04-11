<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_canteen_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('college', 64);
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'college']);
        });

        $catalogKeys = array_keys(config('canteens', []));
        $fallbackSlug = $catalogKeys[0] ?? 'ceit';

        $users = DB::table('users')
            ->select('id', 'college', 'wallet_balance', 'role')
            ->where('role', 'student')
            ->get();

        foreach ($users as $u) {
            $amt = round((float) ($u->wallet_balance ?? 0), 2);
            if ($amt <= 0) {
                continue;
            }

            $college = $u->college && in_array($u->college, $catalogKeys, true)
                ? strtolower((string) $u->college)
                : strtolower($fallbackSlug);

            DB::table('user_canteen_balances')->updateOrInsert(
                ['user_id' => $u->id, 'college' => $college],
                [
                    'balance' => $amt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($users as $u) {
            $total = (float) DB::table('user_canteen_balances')
                ->where('user_id', $u->id)
                ->sum('balance');
            DB::table('users')->where('id', $u->id)->update(['wallet_balance' => round($total, 2)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_canteen_balances');
    }
};
