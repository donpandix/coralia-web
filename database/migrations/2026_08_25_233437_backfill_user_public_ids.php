<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->select('id')
            ->whereNull('public_id')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The generated identifiers are intentionally preserved during rollback.
    }
};
