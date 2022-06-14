<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_msg_id');
            $table->foreign('email_msg_id')->references('id')->on('email_messages')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('sender_id');
            $table->foreign('sender_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('receiver_id');
            $table->foreign('receiver_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('email', 100);
            $table->string('subject', 100);
            $table->text('email_message');
            $table->enum('send_email_after', ['Daily', '7 Days'])->default('Daily');
            $table->datetime('send_at')->nullable();
            $table->datetime('stop_at')->nullable();
            $table->enum('status', ['Pending', 'Sent', 'Stop', 'Failed'])->default('Pending');
            $table->string('status_message', 100)->nullable();
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
        Schema::dropIfExists('email_logs');
    }
}
