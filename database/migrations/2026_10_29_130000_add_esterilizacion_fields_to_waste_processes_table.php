<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEsterilizacionFieldsToWasteProcessesTable extends Migration
{
    public function up()
    {
        Schema::table('waste_processes', function (Blueprint $table) {
            $table->decimal('temp_esterilizador', 8, 2)->nullable();
            $table->decimal('presion_camara_principal', 8, 2)->nullable();
            $table->string('eliminacion_biologica')->nullable();
            $table->string('ciclo_carga')->nullable();
            $table->decimal('rpbi_bi1_cepas', 8, 2)->nullable();
            $table->decimal('rpbi_bi2_punzocortantes', 8, 2)->nullable();
            $table->decimal('rpbi_bi4_no_anatomicos', 8, 2)->nullable();
            $table->decimal('rpbi_bi5_sangre', 8, 2)->nullable();
            $table->decimal('total_procesada', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('waste_processes', function (Blueprint $table) {
            $table->dropColumn([
                'temp_esterilizador',
                'presion_camara_principal',
                'eliminacion_biologica',
                'ciclo_carga',
                'rpbi_bi1_cepas',
                'rpbi_bi2_punzocortantes',
                'rpbi_bi4_no_anatomicos',
                'rpbi_bi5_sangre',
                'total_procesada',
            ]);
        });
    }
}
