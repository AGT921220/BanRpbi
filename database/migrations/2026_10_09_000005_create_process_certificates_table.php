<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_certificates', function (Blueprint $table) {
            $table->increments('id');
            // $table->integer('folio');
            $table->integer('waste_process_id')->unsigned();
            $table->foreign('waste_process_id')->references('id')->on('waste_processes');

            $table->integer('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->integer('manifest_id');
            $table->string('manifest_ids');
            $table->string('manifest_folios');

            $table->string('date');
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
        Schema::dropIfExists('process_certificates');
    }
}
