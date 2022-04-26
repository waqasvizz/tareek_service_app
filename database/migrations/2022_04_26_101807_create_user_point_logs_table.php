<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPointLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_point_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_point_id');
            $table->foreign('user_point_id')->references('id')->on('user_points')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('point_value')->nullable();
            $table->integer('point_target')->nullable();
            $table->integer('total_point_count')->nullable();
            $table->integer('total_point_value')->nullable();
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
        Schema::dropIfExists('user_point_logs');
    }
}