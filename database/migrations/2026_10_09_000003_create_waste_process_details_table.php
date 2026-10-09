<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWasteProcessDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waste_process_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('waste_process_id')->unsigned();

            $table->foreign('waste_process_id')
                ->references('id')
                ->on('waste_processes');

            $table->integer('manifest_id')->unsigned();
            $table->foreign('manifest_id')
                ->references('id')
                ->on('manifests');

            $table->integer('service_detail_id')->unsigned();
            $table->foreign('service_detail_id')
                ->references('id')
                ->on('service_details');

            $table->string('total_weight');
            $table->string('process_weight');    
            

            $table->boolean('is_finished')->default(false);
            $table->boolean('is_completed')->default(false);

            $table->integer('finished_by_id')->unsigned()->nullable();
            $table->foreign('finished_by_id')
                ->references('id')
                ->on('users');
            $table->timestamp('finished_at')->nullable();


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
        Schema::dropIfExists('waste_process_details');
    }
}
