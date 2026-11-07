<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'application_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('application_id')->nullable()->after('id');
                if (Schema::hasTable('applications')) {
                    $table->foreign('application_id')->references('id')->on('applications')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn('application_id');
        });
    }
};
