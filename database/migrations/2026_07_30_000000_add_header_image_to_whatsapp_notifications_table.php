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
                if (!Schema::hasColumn('whatsapp_notifications', 'header_image_path')) {
                    $table->string('header_image_path')->nullable()->after('template_params');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->dropColumn('header_image_path');
        });
    }
};
