<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_notifications')) {
            Schema::table('whatsapp_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('whatsapp_notifications', 'emp_id')) {
                    $table->unsignedBigInteger('emp_id')->nullable()->after('id');
                    $table->foreign('emp_id')->references('id')->on('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('whatsapp_notifications', 'assign_base_id')) {
                    $table->unsignedBigInteger('assign_base_id')->nullable()->after('emp_id');
                    $table->foreign('assign_base_id')->references('id')->on('assign_bases')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->dropForeign(['emp_id']);
            $table->dropForeign(['assign_base_id']);
            $table->dropColumn(['emp_id', 'assign_base_id']);
        });
    }
};
