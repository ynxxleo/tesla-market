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
            $table->decimal('cash_balance', 16, 2)->default(0)->change();
        });

        // Remove the legacy demo balance only from accounts that have never
        // funded, traded, or invested. This preserves balances users earned
        // through approved deposit testing or other activity.
        DB::table('users')
            ->where('cash_balance', 100000)
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('ledger_entries')
                ->whereColumn('ledger_entries.user_id', 'users.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('trades')
                ->whereColumn('trades.user_id', 'users.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('investments')
                ->whereColumn('investments.user_id', 'users.id'))
            ->update(['cash_balance' => 0]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('cash_balance', 16, 2)->default(100000)->change();
        });
    }
};
