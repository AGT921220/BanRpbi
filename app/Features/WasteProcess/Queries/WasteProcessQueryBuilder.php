<?php

namespace App\Features\WasteProcess\Queries;

use App\Models\WasteProcess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WasteProcessQueryBuilder
{
    public function __invoke(): Builder
    {
        return WasteProcess::query()
            ->select([
                'id',
                'date_start',
                'created_at',
                'machine_number',
                'hour_start_machine',
                'hour_machine',
                'user_id',
                'waste_process_type_id',
                'cantidad_solucion_reciclar',
                'acido',
                'sosa_caustica',
                'eca_12',
                'eca_22',
                'eca_1350',
                'eca_49',
                'eca_20pa',
                'sulfactante_c500',
                'solucion_clarif_t42',
                'solucion_flocl_146',
                'ph_inicial',
                'ph_final',
                'lodos_generados_porcentaje',
                'lodos_generados_tnk_lodos',
                'ingreso_filtro_prensa',
                'lodos_generados_disposicion',
                'agua_retorno_proceso_filtro_prensa',
                'ph_lodos',
                'cantidad_total_agua_reciclada',
                'others',
                'solution',
                'is_finished',
                'finished_by_id',
                'finished_at',
                'cantidad_contenedores_proceso_interno',
                'agua_reciclada_presion',
                'contenedores_recuperados',
                'contenedores_inutilizables',
                'agua_residual_proceso',
                'lodos_generados_proceso',
                'solidos_generados_proceso',
                'temperatura_1',
                'temperatura_2',
                'temperatura_3',
                'cantidad_recuperados',
                'porcentaje_recuperado',
                'cantidad_sedimentos',
                'porcentaje_sedimentos',
                'factor_consumo_kw',
                'consumo_kw_hrs',
                'costo_kw_hr',
                'costo_total_operacion',
                'psi',
                'comments',
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
                'temp_esterilizador',
                'presion_camara_principal',
                'eliminacion_biologica',
                'ciclo_carga',
                'rpbi_bi1_cepas',
                'rpbi_bi2_punzocortantes',
                'rpbi_bi4_no_anatomicos',
                'rpbi_bi5_sangre',
                'total_procesada',
            ])
            ->with(['wasteProcessDetails' => function ($query) {
                $query->addSelect([
                    'waste_process_details.*',
                    DB::raw('(SELECT COALESCE(SUM(sub_waste.process_weight), 0)
                          FROM waste_process_details AS sub_waste
                          WHERE sub_waste.service_detail_id = waste_process_details.service_detail_id
                            AND sub_waste.is_finished = 1) AS total_captured_weight'),
                ])
                    ->with([
                        'serviceDetail.rpbiProfile',
                        'manifest.service.client',
                    ]);
            }]);
    }
}
