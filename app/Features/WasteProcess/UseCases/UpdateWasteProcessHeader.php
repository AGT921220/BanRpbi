<?php

namespace App\Features\WasteProcess\UseCases;

use App\Features\WasteProcess\Dto\WasteProcessContainer;
use App\Features\WasteProcess\Dto\WasteProcessPrar;
use App\Features\WasteProcess\Dto\WasteProcessPrs;
use App\Models\WasteProcess;

class UpdateWasteProcessHeader
{
    public function __invoke(
        int $wasteProcessId,
        string $dateStart,
        string $hourStartMachine,
        WasteProcessPrar $wasteProcessPrar,
        WasteProcessContainer $wasteProcessContainer,
        WasteProcessPrs $wasteProcessPrs,
        ?int $machineNumber = null,
        ?string $hourMachine = null,
        ?string $solution = null,
        ?string $comments = null
    ): void {
        $cantidadSolucionReciclar = $wasteProcessPrar->getCantidadSolucionReciclar() ??
            $wasteProcessContainer->getCantidadContenedores();
        $cantidadSolucionReciclar = $cantidadSolucionReciclar ?? $wasteProcessPrs->getCantidadIngresado();
        // dd($wasteProcessContainer->toArray());
        $wasteProcess = WasteProcess::find($wasteProcessId);
        $wasteProcess->machine_number = $machineNumber;
        $wasteProcess->hour_start_machine = $hourStartMachine;
        $wasteProcess->hour_machine = $hourMachine;
        $wasteProcess->date_start = $dateStart;
        $wasteProcess->cantidad_solucion_reciclar = $cantidadSolucionReciclar;
        $wasteProcess->sosa_caustica = $wasteProcessPrar->getSosaCaustica();
        $wasteProcess->sulfactante_c500 = $wasteProcessPrar->getSulfactanteC500();
        $wasteProcess->solucion_clarif_t42 = $wasteProcessPrar->getSolucionClarifT42();
        $wasteProcess->solucion_flocl_146 = $wasteProcessPrar->getSolucionFlocl146();
        $wasteProcess->ingreso_filtro_prensa = $wasteProcessPrar->getIngresoFiltroPrensa();
        $wasteProcess->acido = $wasteProcessPrar->getAcido();
        $wasteProcess->ph_inicial = $wasteProcessPrar->getPhInicial();
        $wasteProcess->ph_final = $wasteProcessPrar->getPhFinal();
        $wasteProcess->ph_lodos = $wasteProcessPrar->getPhLodos();
        $wasteProcess->eca_12 = $wasteProcessPrar->getEca12();
        $wasteProcess->eca_22 = $wasteProcessPrar->getEca22();
        $wasteProcess->eca_1350 = $wasteProcessPrar->getEca1350();
        $wasteProcess->eca_49 = $wasteProcessPrar->getEca49();
        $wasteProcess->eca_20pa = $wasteProcessPrar->getEca20pa();
        $wasteProcess->lodos_generados_porcentaje = $wasteProcessPrar->getLodosGeneradosPorcentaje();
        $wasteProcess->lodos_generados_tnk_lodos = $wasteProcessPrar->getLodosGeneradosTnk();
        $wasteProcess->lodos_generados_disposicion = $wasteProcessPrar->getLodosGeneradosDisposicion();
        $wasteProcess->agua_retorno_proceso_filtro_prensa = $wasteProcessPrar->getAguaRetornoProcesoFiltroPrensa();
        $wasteProcess->cantidad_total_agua_reciclada = $wasteProcessPrar->getCantidadTotalAguaReciclada();
        $wasteProcess->others = $wasteProcessPrar->getOthers();

        $wasteProcess->cantidad_contenedores_proceso_interno =
            $wasteProcessContainer->getCantidadContenedoresProcesoInterno();
        $wasteProcess->agua_reciclada_presion = $wasteProcessContainer->getAguaRecicladaPresion();
        $wasteProcess->contenedores_recuperados = $wasteProcessContainer->getContenedoresRecuperados();
        $wasteProcess->contenedores_inutilizables = $wasteProcessContainer->getContenedoresInutilizables();
        $wasteProcess->agua_residual_proceso = $wasteProcessContainer->getAguaResidualProceso();
        $wasteProcess->lodos_generados_proceso = $wasteProcessContainer->getLodosGeneradosProceso();
        $wasteProcess->solidos_generados_proceso = $wasteProcessContainer->getSolidosGeneradosProceso();
        $wasteProcess->psi = $wasteProcessContainer->getPsi();
        $wasteProcess->solution = $solution;

        $wasteProcess->temperatura_1 = $wasteProcessPrs->getTemperatura1();
        $wasteProcess->temperatura_2 = $wasteProcessPrs->getTemperatura2();
        $wasteProcess->temperatura_3 = $wasteProcessPrs->getTemperatura3();
        $wasteProcess->cantidad_recuperados = $wasteProcessPrs->getCantidadRecuperados();
        $wasteProcess->porcentaje_recuperado = $wasteProcessPrs->getPorcentajeRecuperado();
        $wasteProcess->cantidad_sedimentos = $wasteProcessPrs->getCantidadSedimentos();
        $wasteProcess->porcentaje_sedimentos = $wasteProcessPrs->getPorcentajeSedimentos();
        $wasteProcess->factor_consumo_kw = $wasteProcessPrs->getFactorConsumoKw();
        $wasteProcess->consumo_kw_hrs = $wasteProcessPrs->getConsumoKwHrs();
        $wasteProcess->costo_kw_hr = $wasteProcessPrs->getCostoKwHr();
        $wasteProcess->costo_total_operacion = $wasteProcessPrs->getCostoTotalOperacion();
        $wasteProcess->comments = $comments;
        $wasteProcess->save();
    }
}
