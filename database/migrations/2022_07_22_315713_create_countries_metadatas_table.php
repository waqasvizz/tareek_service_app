<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesMetadatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries_metadatas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 100);
            $table->string('iso_code', 100);
            $table->string('state_required', 20)->nullable();
            $table->string('postcode_required', 20)->nullable();
            $table->string('intl_calling_number', 20);
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
        Schema::dropIfExists('countries_metadatas');
    }
}
