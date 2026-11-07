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
        if (!Schema::hasTable('whatsapp_notification_recipients')) {
            Schema::create('whatsapp_notification_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('whatsapp_notification_id')->constrained('whatsapp_notifications', 'id', 'wa_recipients_notification_fk')->cascadeOnDelete();
                $table->foreignId('whatsapp_contact_id')->constrained('whatsapp_contacts', 'id', 'wa_recipients_contact_fk')->cascadeOnDelete();
                $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
                $table->string('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_recipients');
    }
};
