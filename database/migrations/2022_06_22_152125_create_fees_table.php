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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->date('date');
            $table->bigInteger('amount');
            $table->bigInteger('fee');
            $table->bigInteger('interest');
            $table->bigInteger('amortization');
            $table->bigInteger('credit_due');
            $table->bigInteger('due');
            $table->bigInteger('interest_due');
            $table->bigInteger('late_due')->default(0);
            $table->string('state')->default('created');
            $table->double('late_interest_rate')->default(0);
            $table->double('late_interest_pay')->default(0);
            $table->double('late_interest_paid')->default(0);
            $table->foreignId('credit_id')->constrained('credits')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fees');
    }
};
