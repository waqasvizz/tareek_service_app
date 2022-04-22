<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStripeInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_stripe_informations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('stripe_mode', ['Test', 'Live'])->default('Live');
            $table->longText('pk_test')->comment('Publishable Key')->nullable();
            $table->longText('sk_test')->comment('Secret Key')->nullable();
            $table->longText('pk_live')->comment('Publishable Key')->nullable();
            $table->longText('sk_live')->comment('Secret Key')->nullable();
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
        Schema::dropIfExists('user_stripe_informations');
    }
}