<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('assign_base_id')->nullable()->after('id');
            $table->foreign('assign_base_id')->references('id')->on('assign_bases')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['assign_base_id']);
            $table->dropColumn('assign_base_id');
        });
    }
};
