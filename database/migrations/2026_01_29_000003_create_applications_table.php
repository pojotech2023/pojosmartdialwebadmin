<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g., "Bank", "Telecalling"
                $table->timestamps();
            });

            // Seed initial data
            DB::table('applications')->insert([
                ['name' => 'Bank', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Telecalling', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('applications');
    }
};
