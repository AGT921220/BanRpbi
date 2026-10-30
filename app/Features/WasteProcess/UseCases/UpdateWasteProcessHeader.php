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
        $wasteProcess = WasteProcess::find($wasteProcessId);
        $wasteProcess->machine_number = $machineNumber;
        $wasteProcess->hour_start_machine = $hourStartMachine;
        $wasteProcess->hour_machine = $hourMachine;
        $wasteProcess->date_start = $dateStart;
        if ($wasteProcessContainer->getCantidadContenedores() !== null) {
            $wasteProcess->cantidad_solucion_reciclar = $wasteProcessContainer->getCantidadContenedores();
        }

        foreach ($wasteProcessPrar->attributes() as $column => $value) {
            $wasteProcess->{$column} = $value;
        }

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

        foreach ($wasteProcessPrs->attributes() as $column => $value) {
            $wasteProcess->{$column} = $value;
        }
        $wasteProcess->comments = $comments;
        $wasteProcess->save();
    }
}
