<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('asset_type');
            $table->foreign('asset_type')->references('id')->on('user_assets_categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('filename', 255);
            $table->string('filepath', 255);
            $table->string('mimetypes', 255);
            $table->tinyInteger('asset_status')->comment('1=verified, 0=not-verified')->default(0);
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
        Schema::dropIfExists('user_assets');
    }
}