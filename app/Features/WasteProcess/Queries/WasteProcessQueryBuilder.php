<?php

namespace App\Features\WasteProcess\Queries;

use App\Models\WasteProcess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WasteProcessQueryBuilder
{
    public function __invoke():Builder
    {
        return WasteProcess::select(
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
            'others',
            'psi',
            'comments'
        )
            ->with(['wasteProcessDetails' => function ($query) {
                $query->addSelect([
                    'waste_process_details.*', // Incluye todos los campos de wasteProcessDetails
                    DB::raw('(SELECT SUM(sub_waste.process_weight) 
                          FROM waste_process_details AS sub_waste 
                          WHERE sub_waste.manifest_detail_id = waste_process_details.manifest_detail_id 
                            AND sub_waste.manifest_id = waste_process_details.manifest_id) AS total_captured_weight')
                ])
                ->with('manifest.client')
                    ->with(['manifestDetail' => function ($query) {
                        $query->with('clientProfile');
                    }]);
            }]);
    }
}
