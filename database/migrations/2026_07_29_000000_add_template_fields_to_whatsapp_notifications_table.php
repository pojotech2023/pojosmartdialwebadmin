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
                if (!Schema::hasColumn('whatsapp_notifications', 'template_language')) {
                    $table->string('template_language')->nullable()->after('template_name');
                }
                if (!Schema::hasColumn('whatsapp_notifications', 'template_params')) {
                    $table->json('template_params')->nullable()->after('message');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            $table->dropColumn(['template_language', 'template_params']);
        });
    }
};
