<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title', 255);
            $table->string('price', 255);
            $table->string('product_img', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('lat', 255)->nullable();
            $table->string('long', 255)->nullable();
            $table->enum('product_type', ['single', 'bulk'])->default('single');
            $table->string('bulk_qty', 100)->nullable();
            $table->string('consume_qty', 100)->nullable();
            $table->string('min_qty', 100)->nullable();
            $table->string('min_discount', 100)->nullable();
            $table->string('max_qty', 100)->nullable();
            $table->string('max_discount', 100)->nullable();
            $table->date('expiry_time')->nullable();
            $table->text('description')->nullable();
            $table->string('contact', 255)->nullable();
            $table->string('avg_rating',20)->nullable();
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
        Schema::dropIfExists('products');
    }
}