<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create products table
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default products requested by user
            DB::table('products')->insert([
                ['name' => 'Website', 'description' => 'Website design and development solutions', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Ecommerce', 'description' => 'E-commerce platform and online store solutions', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Construction Management Software', 'description' => 'Software solutions for construction and site management', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Add product_id and product_type to assign_bases table
        Schema::table('assign_bases', function (Blueprint $table) {
            if (!Schema::hasColumn('assign_bases', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('company_name');
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            }
            if (!Schema::hasColumn('assign_bases', 'product_type')) {
                $table->string('product_type')->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assign_bases', function (Blueprint $table) {
            if (Schema::hasColumn('assign_bases', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn(['product_id']);
            }
            if (Schema::hasColumn('assign_bases', 'product_type')) {
                $table->dropColumn(['product_type']);
            }
        });

        Schema::dropIfExists('products');
    }
};
