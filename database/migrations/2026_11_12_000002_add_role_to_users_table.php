<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('Telecaller')->after('password');
            }
        });

        // Set role for Admin
        DB::table('users')
            ->where('id', 10)
            ->orWhere('email', 'admin@gmail.com')
            ->orWhere('name', 'Admin')
            ->update(['role' => 'Admin']);

        // Set remaining null roles to Telecaller
        DB::table('users')->whereNull('role')->update(['role' => 'Telecaller']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
