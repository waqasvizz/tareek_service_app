<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('order_type', ['Service', 'Product']);
            $table->enum('order_status', ['Pending', 'Request accepted', 'Request rejected', 'On the way', 'In-progress', 'Completed']);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_multiple_address_id');
            $table->foreign('user_multiple_address_id')->references('id')->on('user_multiple_addresses')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_delivery_option_id');
            $table->foreign('user_delivery_option_id')->references('id')->on('user_delivery_options')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_card_id');
            $table->foreign('user_card_id')->references('id')->on('user_cards')->onUpdate('cascade')->onDelete('cascade');
            $table->double('grand_total');
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
        Schema::dropIfExists('orders');
    }
}