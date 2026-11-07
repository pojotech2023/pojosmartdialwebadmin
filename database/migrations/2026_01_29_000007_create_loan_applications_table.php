<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('loan_applications')) {
            Schema::create('loan_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assign_base_id');
                $table->date('login_date')->nullable();
                $table->string('app_number')->nullable();
                $table->string('logged_bank')->nullable();
                $table->string('promo_code')->nullable();
                $table->decimal('req_amount', 12, 2)->nullable();
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'IN_PROCESS'])->default('PENDING');
                $table->text('remarks')->nullable();
                $table->decimal('net_salary', 10, 2)->nullable();
                $table->string('bank_ac')->nullable();
                $table->text('add_proof')->nullable();
                $table->string('category')->nullable();
                $table->string('bank_manager')->nullable();
                $table->string('dsa_name')->nullable();
                $table->string('ref_1')->nullable();
                $table->string('ref_2')->nullable();
                $table->integer('tenure')->nullable();
                $table->string('location')->nullable();
                $table->text('discr')->nullable();
                $table->date('date')->nullable();
                $table->string('mode')->nullable();
                $table->string('aadhar_card_front_upload')->nullable();
                $table->string('aadhar_card_back_upload')->nullable();
                $table->string('pan_upload')->nullable();
                $table->string('bank_statement_upload')->nullable();
                $table->string('salary_slip_upload')->nullable();
                $table->timestamps();
                
                $table->foreign('assign_base_id')->references('id')->on('assign_bases')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('loan_applications');
    }
};