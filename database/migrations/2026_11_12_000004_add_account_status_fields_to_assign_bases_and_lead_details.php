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
        // 1. Add account status fields to assign_bases
        Schema::table('assign_bases', function (Blueprint $table) {
            if (!Schema::hasColumn('assign_bases', 'account_status')) {
                $table->string('account_status', 100)->default('NEW')->after('status');
            }
            if (!Schema::hasColumn('assign_bases', 'account_remarks')) {
                $table->text('account_remarks')->nullable()->after('account_status');
            }
            if (!Schema::hasColumn('assign_bases', 'account_updated_by')) {
                $table->unsignedBigInteger('account_updated_by')->nullable()->after('account_remarks');
                $table->foreign('account_updated_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('assign_bases', 'account_updated_at')) {
                $table->timestamp('account_updated_at')->nullable()->after('account_updated_by');
            }
        });

        // 2. Add account status fields to lead_details
        Schema::table('lead_details', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_details', 'account_status')) {
                $table->string('account_status', 100)->default('NEW')->after('gst_no');
            }
            if (!Schema::hasColumn('lead_details', 'account_remarks')) {
                $table->text('account_remarks')->nullable()->after('account_status');
            }
            if (!Schema::hasColumn('lead_details', 'account_updated_by')) {
                $table->unsignedBigInteger('account_updated_by')->nullable()->after('account_remarks');
                $table->foreign('account_updated_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('lead_details', 'account_updated_at')) {
                $table->timestamp('account_updated_at')->nullable()->after('account_updated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_details', function (Blueprint $table) {
            if (Schema::hasColumn('lead_details', 'account_updated_by')) {
                $table->dropForeign(['account_updated_by']);
            }
            $table->dropColumn([
                'account_status',
                'account_remarks',
                'account_updated_by',
                'account_updated_at',
            ]);
        });

        Schema::table('assign_bases', function (Blueprint $table) {
            if (Schema::hasColumn('assign_bases', 'account_updated_by')) {
                $table->dropForeign(['account_updated_by']);
            }
            $table->dropColumn([
                'account_status',
                'account_remarks',
                'account_updated_by',
                'account_updated_at',
            ]);
        });
    }
};
