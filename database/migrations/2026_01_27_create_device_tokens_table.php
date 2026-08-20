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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_id');
            $table->string('device_token');
            $table->string('device_type')->default('android'); // android, ios, web
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('emp_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['emp_id', 'device_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
