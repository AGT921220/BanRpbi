<?php

namespace App\Features\WasteProcess\UseCases;

use App\Bussines\Shared\Infrastructure\BuilderFilter;
use App\Features\WasteProcess\Dto\WasteProcess as DtoWasteProcess;
use App\Features\WasteProcess\Helpers\BuildWasteProcess;
use App\Features\WasteProcess\Queries\WasteProcessQueryBuilder;
use App\Models\WasteProcess;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SearchWasteProcess
{
    private $buildWasteProcess;
    private $builderFilter;
    public function __construct(
        BuildWasteProcess $buildWasteProcess,
        BuilderFilter $builderFilter
    ) {
        $this->buildWasteProcess = $buildWasteProcess;
        $this->builderFilter = $builderFilter;
    }

    public function __invoke(?array $filters = []): array
    {
        $baseQuery = $this->builderFilter->__invoke(
            (new WasteProcessQueryBuilder())->__invoke(),
            $filters
        );
        $data = $baseQuery->get();
        $totals = $this->getTotals($filters);
        $data = $data->map(function ($item) {
            return $this->buildWasteProcess->__invoke($item)->toArray();
        })->toArray();

        return
            [
                'data' => $data,
                'totals' => $totals
            ];
        return $data;
    }
    private function getTotals($filters): array
    {
        // dd($this->builderFilter->__invoke(
        //     WasteProcess::select(
        //         DB::raw('SUM(cantidad_solucion_reciclar) as cantidad_solucion_reciclar'),
        //         DB::raw('SUM(acido) as acido'),
        //         DB::raw('SUM(sosa_caustica) as sosa_caustica'),
        //         DB::raw('SUM(eca_12) as eca_12'),
        //         DB::raw('SUM(eca_22) as eca_22'),
        //         DB::raw('SUM(eca_1350) as eca_1350'),
        //         DB::raw('SUM(eca_49) as eca_49'),
        //         DB::raw('SUM(eca_20pa) as eca_20pa'),
        //         DB::raw('SUM(sulfactante_c500) as sulfactante_c500'),
        //         DB::raw('SUM(solucion_clarif_t42) as solucion_clarif_t42'),
        //         DB::raw('SUM(solucion_flocl_146) as solucion_flocl_146'),
        //         DB::raw('SUM(ph_inicial) as ph_inicial'),
        //         DB::raw('SUM(ph_final) as ph_final'),
        //         DB::raw('SUM(lodos_generados_porcentaje) as lodos_generados_porcentaje'),
        //         DB::raw('SUM(lodos_generados_tnk_lodos) as lodos_generados_tnk_lodos'),
        //         DB::raw('SUM(ingreso_filtro_prensa) as ingreso_filtro_prensa'),
        //         DB::raw('SUM(lodos_generados_disposicion) as lodos_generados_disposicion'),
        //         DB::raw('SUM(agua_retorno_proceso_filtro_prensa) as agua_retorno_proceso_filtro_prensa'),
        //         DB::raw('SUM(ph_lodos) as ph_lodos'),
        //         DB::raw('SUM(cantidad_total_agua_reciclada) as cantidad_total_agua_reciclada'),
        //         DB::raw('SUM(others) as others'),
        //         DB::raw('SUM(cantidad_ingresado) as cantidad_ingresado'),
        //         DB::raw('AVG(porcentaje_sedimentos) as porcentaje_sedimentos'),
        //         DB::raw('AVG(porcentaje_recuperado) as porcentaje_recuperado')
        //     ),
        //     $filters
        // )->toRawSql());
        $totals = $this->builderFilter->__invoke(
            WasteProcess::select(
                DB::raw('SUM(cantidad_solucion_reciclar) as cantidad_solucion_reciclar'),
                DB::raw('SUM(acido) as acido'),
                DB::raw('SUM(sosa_caustica) as sosa_caustica'),
                DB::raw('SUM(eca_12) as eca_12'),
                DB::raw('SUM(eca_22) as eca_22'),
                DB::raw('SUM(eca_1350) as eca_1350'),
                DB::raw('SUM(eca_49) as eca_49'),
                DB::raw('SUM(eca_20pa) as eca_20pa'),
                DB::raw('SUM(sulfactante_c500) as sulfactante_c500'),
                DB::raw('SUM(solucion_clarif_t42) as solucion_clarif_t42'),
                DB::raw('SUM(solucion_flocl_146) as solucion_flocl_146'),
                DB::raw('SUM(ph_inicial) as ph_inicial'),
                DB::raw('SUM(ph_final) as ph_final'),
                DB::raw('SUM(lodos_generados_porcentaje) as lodos_generados_porcentaje'),
                DB::raw('SUM(lodos_generados_tnk_lodos) as lodos_generados_tnk_lodos'),
                DB::raw('SUM(ingreso_filtro_prensa) as ingreso_filtro_prensa'),
                DB::raw('SUM(lodos_generados_disposicion) as lodos_generados_disposicion'),
                DB::raw('SUM(agua_retorno_proceso_filtro_prensa) as agua_retorno_proceso_filtro_prensa'),
                DB::raw('SUM(ph_lodos) as ph_lodos'),
                DB::raw('SUM(cantidad_total_agua_reciclada) as cantidad_total_agua_reciclada'),
                DB::raw('SUM(others) as others'),
                DB::raw('SUM(cantidad_recuperados) as cantidad_recuperados'),
                DB::raw('SUM(cantidad_solucion_reciclar) as cantidad_ingresado'),
                DB::raw('SUM(cantidad_sedimentos) as cantidad_sedimentos'),
                DB::raw('SUM(consumo_kw_hrs) as consumo_kw_hrs'),
                DB::raw('SUM(costo_kw_hr) as costo_kw_hr'),
                DB::raw('SUM(costo_total_operacion) as costo_total_operacion'),
                DB::raw('AVG(porcentaje_sedimentos) as porcentaje_sedimentos'),
                DB::raw('AVG(porcentaje_recuperado) as porcentaje_recuperado')
            ),
            $filters
        )->first()->toArray();

        return array_map(function ($value) {
            // $val = $value;
            // if ($val === null) {
            //     return '0.00';
            // }
            return BigDecimal::of((float)$value)->toScale(2, \Brick\Math\RoundingMode::HALF_UP)->__toString();
        }, $totals);
    }
}
