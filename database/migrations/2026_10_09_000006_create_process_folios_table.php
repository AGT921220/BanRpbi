<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessFoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_folios', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients');

            $table->integer('process_certificate_id')->unsigned();
            $table->foreign('process_certificate_id')->references('id')->on('process_certificates');

            $table->integer('waste_process_id')->unsigned();
            $table->foreign('waste_process_id')->references('id')->on('waste_processes');




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
        Schema::dropIfExists('process_folios');
    }
}
