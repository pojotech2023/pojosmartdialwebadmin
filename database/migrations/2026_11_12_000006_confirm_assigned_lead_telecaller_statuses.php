<?php

use Illuminate\Database\Migrations\Migration;
return new class extends Migration
{
    public function up(): void
    {
        // Account assignment must not alter the telecaller status.
    }

    public function down(): void
    {
        // Do not change telecaller statuses when rolling back this data migration.
    }
};
