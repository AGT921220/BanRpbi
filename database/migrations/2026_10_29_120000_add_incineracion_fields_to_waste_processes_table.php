<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncineracionFieldsToWasteProcessesTable extends Migration
{
    public function up()
    {
        Schema::table('waste_processes', function (Blueprint $table) {
            $table->decimal('temp_camara_primaria', 8, 2)->nullable();
            $table->decimal('temp_camara_secundaria', 8, 2)->nullable();
            $table->decimal('temp_entrada_venturi', 8, 2)->nullable();
            $table->decimal('temp_salida_venturi', 8, 2)->nullable();
            $table->decimal('temp_salida_chimenea', 8, 2)->nullable();
            $table->decimal('oxigeno', 8, 2)->nullable();
            $table->decimal('presion_negativa', 8, 2)->nullable();
            $table->decimal('consumo_gas_lp', 8, 2)->nullable();
            $table->decimal('consumo_promedio_agua', 8, 2)->nullable();
            $table->decimal('evaporacion_agua_tratada', 8, 2)->nullable();
            $table->decimal('patologicos', 8, 2)->nullable();
            $table->decimal('cultivos_cepas', 8, 2)->nullable();
            $table->decimal('sangre', 8, 2)->nullable();
            $table->decimal('ceniza_generada', 8, 2)->nullable();
            $table->decimal('descarga_lavador', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('waste_processes', function (Blueprint $table) {
            $table->dropColumn([
                'temp_camara_primaria',
                'temp_camara_secundaria',
                'temp_entrada_venturi',
                'temp_salida_venturi',
                'temp_salida_chimenea',
                'oxigeno',
                'presion_negativa',
                'consumo_gas_lp',
                'consumo_promedio_agua',
                'evaporacion_agua_tratada',
                'patologicos',
                'cultivos_cepas',
                'sangre',
                'ceniza_generada',
                'descarga_lavador',
            ]);
        });
    }
}
