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
        if (Schema::hasTable('loan_applications')) {
            Schema::table('loan_applications', function (Blueprint $table) {
                // Add new columns for front and back Aadhar uploads if not exists
                if (!Schema::hasColumn('loan_applications', 'aadhar_card_front_upload')) {
                    $table->string('aadhar_card_front_upload')->nullable()->after('mode');
                }
                if (!Schema::hasColumn('loan_applications', 'aadhar_card_back_upload')) {
                    $table->string('aadhar_card_back_upload')->nullable()->after('aadhar_card_front_upload');
                }
                
                // Drop old single aadhar_card_upload column if it exists
                if (Schema::hasColumn('loan_applications', 'aadhar_card_upload')) {
                    $table->dropColumn('aadhar_card_upload');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Restore old column
            $table->string('aadhar_card_upload')->nullable()->after('mode');
            
            // Drop new columns
            $table->dropColumn(['aadhar_card_front_upload', 'aadhar_card_back_upload']);
        });
    }
};
