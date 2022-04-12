<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->date('date_of_birth')->nullable();
            $table->string('profile_image', 255)->nullable();
            $table->string('phone_number', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('company_number', 255)->nullable();
            $table->string('company_documents', 255)->nullable();
            $table->text('user_type')->comment('app, facebook, google')->default('app');
            $table->text('address')->nullable();
            $table->string('latitude', 255)->nullable();
            $table->string('longitude', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('account_status')->comment('1=active, 0=block')->default(1);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}