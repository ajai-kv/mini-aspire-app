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
        Schema::create('repayment_schedule', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('loan_id');
            $table->decimal('amount', 25, 10);
            $table->timestampTz('due_date');
            $table->string('status');
            $table->timestampTz('deleted_at')->nullable();
            $table->timestampsTz();
            $table->foreign('loan_id')->references('id')->on('loan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayment_schedule');
    }
};
