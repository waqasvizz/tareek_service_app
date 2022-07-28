<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('sender_user_id')->unsigned();
            $table->foreign('sender_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('receiver_user_id')->unsigned();
            $table->foreign('receiver_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('currency',20);
            $table->double('total_amount_captured');
            $table->double('admin_amount_captured');
            $table->double('provider_amount_captured');
            $table->longText('admin_response_object')->nullable();
            $table->longText('provider_response_object')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}