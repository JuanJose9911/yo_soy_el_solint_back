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
        Schema::create('credits', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('customer_id')->constrained('customers');
            $table->integer('pagare_number');
            $table->double('amount');
            $table->double('loan_amount');
            $table->double('initial_fee');
            $table->double('due');
            $table->float('interest_rate');
            $table->integer('monthly_fees');
            $table->text('notes')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->string('state')->default('active');
            $table->text('inactivation_reason')->default(null)->nullable();
            $table->date('date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *pagare number
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credits');
    }
};
