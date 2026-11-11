<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'status')) {
                $table->string('status', 100)->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('notifications', 'appointment_date')) {
                $table->string('appointment_date', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('notifications', 'appointment_time')) {
                $table->string('appointment_time', 50)->nullable()->after('appointment_date');
            }
            if (!Schema::hasColumn('notifications', 'location')) {
                $table->text('location')->nullable()->after('appointment_time');
            }
            if (!Schema::hasColumn('notifications', 'alternate_number')) {
                $table->string('alternate_number', 50)->nullable()->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['status', 'appointment_date', 'appointment_time', 'location', 'alternate_number'] as $col) {
                if (Schema::hasColumn('notifications', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
