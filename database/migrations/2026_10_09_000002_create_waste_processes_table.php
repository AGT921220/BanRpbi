<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWasteProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waste_processes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->integer('waste_process_type_id')->unsigned()->nullable();
            $table->foreign('waste_process_type_id')
                ->references('id')
                ->on('waste_process_types');

            $table->date('date_start');


            $table->time('hour_start_machine')->nullable();
            $table->time('hour_machine')->nullable();
            $table->integer('machine_number')->unsigned()->nullable();
            $table->foreign('machine_number')
                ->references('id')
                ->on('reactors');


                $table->string('solution')->nullable();

                //SOLUCIONES GASTADAS
                $table->decimal('cantidad_solucion_reciclar', 8, 2)->nullable();
                $table->decimal('acido', 8, 2)->nullable();
                $table->decimal('sosa_caustica', 8, 2)->nullable();
                $table->decimal('eca_12', 8, 2)->nullable();
                $table->decimal('eca_22', 8, 2)->nullable();
                $table->decimal('eca_1350', 8, 2)->nullable();
                $table->decimal('eca_49', 8, 2)->nullable();
                $table->decimal('eca_20pa', 8, 2)->nullable();
                $table->decimal('sulfactante_c500', 8, 2)->nullable();
                $table->decimal('solucion_clarif_t42', 8, 2)->nullable();
                $table->decimal('solucion_flocl_146', 8, 2)->nullable();
                $table->decimal('ph_inicial', 5, 2)->nullable();
                $table->decimal('ph_final', 5, 2)->nullable();
                $table->decimal('lodos_generados_porcentaje', 5, 2)->nullable();
                $table->decimal('lodos_generados_tnk_lodos', 8, 2)->nullable();
                $table->decimal('ingreso_filtro_prensa', 8, 2)->nullable();
                $table->decimal('lodos_generados_disposicion', 8, 2)->nullable();
                $table->decimal('agua_retorno_proceso_filtro_prensa', 8, 2)->nullable();
                $table->decimal('ph_lodos', 8, 2)->nullable();
                $table->decimal('cantidad_total_agua_reciclada', 8, 2)->nullable();


                #CONTENEDORES
                $table->decimal('cantidad_contenedores', 8, 2)->nullable();
                $table->decimal('cantidad_contenedores_proceso_interno', 8, 2)->nullable();
                $table->decimal('agua_reciclada_presion', 8, 2)->nullable();
                $table->decimal('contenedores_recuperados', 8, 2)->nullable();
                $table->decimal('contenedores_inutilizables', 8, 2)->nullable();
                $table->decimal('agua_residual_proceso', 8, 2)->nullable();
                $table->decimal('lodos_generados_proceso', 8, 2)->nullable();
                $table->decimal('solidos_generados_proceso', 8, 2)->nullable();


                #PRS
                $table->decimal('cantidad_ingresado', 8, 2)->nullable();
                $table->decimal('temperatura_1', 8, 2)->nullable();
                $table->decimal('temperatura_2', 8, 2)->nullable();
                $table->decimal('temperatura_3', 8, 2)->nullable();
                $table->decimal('cantidad_recuperados', 8, 2)->nullable();
                $table->decimal('porcentaje_recuperado', 5, 2)->nullable();
                $table->decimal('cantidad_sedimentos', 8, 2)->nullable();
                $table->decimal('porcentaje_sedimentos', 5, 2)->nullable();
                $table->decimal('factor_consumo_kw', 8, 2)->nullable();
                $table->decimal('consumo_kw_hrs', 8, 2)->nullable();
                $table->decimal('costo_kw_hr', 8, 2)->nullable();
                $table->decimal('costo_total_operacion', 8, 2)->nullable();
                $table->decimal('others', 8, 2)->nullable();

                $table->boolean('is_finished')->default(false);
                $table->integer('finished_by_id')->unsigned()->nullable();
                $table->foreign('finished_by_id')
                    ->references('id')
                    ->on('users');
                $table->timestamp('finished_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('waste_processes');
    }
}
