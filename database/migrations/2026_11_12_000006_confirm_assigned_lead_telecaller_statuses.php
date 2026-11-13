<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('assign_bases')
            ->whereNotNull('assigned_to')
            ->update(['status' => 'CONFIRMED']);
    }

    public function down(): void
    {
        // Do not change telecaller statuses when rolling back this data migration.
    }
};
