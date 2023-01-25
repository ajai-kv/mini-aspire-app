<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('loan_reference_number');
            $table->uuid('customer_id');
            $table->integer('tenure');
            $table->string('tenure_type');
            $table->integer('amount');
            $table->uuid('approved_by')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->string('reject_reason')->nullable();
            $table->string('status');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan');
    }
};
