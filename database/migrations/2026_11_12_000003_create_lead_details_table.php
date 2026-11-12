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
        // 1. Add assigned_to column to assign_bases table if not present
        Schema::table('assign_bases', function (Blueprint $table) {
            if (!Schema::hasColumn('assign_bases', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('emp_id');
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            }
        });

        // 2. Create lead_details table
        if (!Schema::hasTable('lead_details')) {
            Schema::create('lead_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assign_base_id')->unique();
                $table->unsignedBigInteger('emp_id')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->string('project_type')->nullable();
                $table->string('cus_company_name')->nullable();
                $table->string('cus_mail')->nullable();
                $table->string('price', 50)->nullable();
                $table->string('gst', 5)->default('N');
                $table->string('gst_no', 50)->nullable();
                $table->timestamps();

                $table->foreign('assign_base_id')->references('id')->on('assign_bases')->onDelete('cascade');
                $table->foreign('emp_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_details');

        Schema::table('assign_bases', function (Blueprint $table) {
            if (Schema::hasColumn('assign_bases', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
        });
    }
};
