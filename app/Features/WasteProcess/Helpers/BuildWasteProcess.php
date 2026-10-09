<?php

namespace App\Features\WasteProcess\Helpers;

use App\Features\WasteProcess\Dto\WasteProcess as DtoWasteProcess;
use App\Features\WasteProcess\Dto\WasteProcessDate;
use App\Features\WasteProcess\Dto\WasteProcessFinish;
use App\Features\WasteProcess\Dto\WasteProcessHeader;
use App\Features\WasteProcess\Dto\WasteProcessMachine;
use App\Manifest;
use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use App\Models\WasteProcessType;
use Illuminate\Support\Carbon;

class BuildWasteProcess
{
    public function __invoke(WasteProcess $wasteProcess): DtoWasteProcess
    {
        $wateProcessDate = new WasteProcessDate($wasteProcess->date_start, $wasteProcess->created_at);
        $wasteProcessMachine = new WasteProcessMachine(
            $wasteProcess->machine_number,
            Carbon::parse($wasteProcess->hour_start_machine)->format('H:i'),
            Carbon::parse($wasteProcess->hour_machine)->format('H:i')
        );
        $wasteProcessFinished = new WasteProcessFinish(
            $wasteProcess->is_finished,
            $wasteProcess->finished_by_id,
            $wasteProcess->finished_at
        );
        $wasteProcessHeader = new WasteProcessHeader(
            $wasteProcess->id,
            $wateProcessDate,
            $wasteProcessMachine,
            $wasteProcessFinished,
            $wasteProcess->user_id,
            $wasteProcess->waste_process_type_id,
            $wasteProcess->solution,
            $wasteProcess->comments
        );

        return new DtoWasteProcess(
            $wasteProcessHeader,
            $this->getParams($wasteProcess),
            $this->getDetails($wasteProcess)
        );
    }
    private function getParams($wasteProcess): array
    {
        $manifestIds = $wasteProcess->wasteProcessDetails->pluck('manifest_id')->unique();
        $manifestFolios = Manifest::whereIn('id', $manifestIds)->pluck('folio')->implode(',');

        if ($wasteProcess->waste_process_type_id == WasteProcessType::PROCESS_INCINERACION) {
            return $this->getIncineracionDetails($wasteProcess, $manifestFolios);
        }

        if ($wasteProcess->waste_process_type_id == WasteProcessType::PROCESS_ESTERILIZACION) {
            return $this->getEsterilizacionDetails($wasteProcess, $manifestFolios);
        }

        return [];
    }
    private function getDetails(WasteProcess $wasteProcess): array
    {
        return $wasteProcess->wasteProcessDetails->map(function (WasteProcessDetail $detail) {
            return
                [
                    'id' => $detail->id,
                    'total_weight' => $detail->total_weight,
                    'process_weight' => $detail->process_weight,
                    'captured_weight' => $detail->total_captured_weight,
                    'remaing_weight' => $detail->total_weight - $detail->total_captured_weight,
                    'profile' => $detail->manifestDetail->clientProfile->name
                ];
        })->toArray();
    }
    private function getEsterilizacionDetails(WasteProcess $wasteProcess, string $manifestFolios): array
    {
        return [
            [
                // 'waste_process_id' => $wasteProcess->waste_process_id,
                // 'manifest_detail_id' => $wasteProcess->manifest_detail_id,
                'id' => $wasteProcess->id,
                // 'manifest_id' => $wasteProcess->wasteProcessDetails->pluck('manifest_id')->unique()->implode(','),
                'manifest_id' => $manifestFolios,
                'cantidad_solucion_reciclar' => $wasteProcess->cantidad_solucion_reciclar ?? 0,
                'acido' => $wasteProcess->acido ?? 0,
                'sosa_caustica' => $wasteProcess->sosa_caustica ?? 0,
                'eca_12' => $wasteProcess->eca_12 ?? 0,
                'eca_22' => $wasteProcess->eca_22 ?? 0,
                'eca_1350' => $wasteProcess->eca_1350 ?? 0,
                'eca_49' => $wasteProcess->eca_49 ?? 0,
                'eca_20pa' => $wasteProcess->eca_20pa ?? 0,
                'sulfactante_c500' => $wasteProcess->sulfactante_c500 ?? 0,
                'solucion_clarif_t42' => $wasteProcess->solucion_clarif_t42 ?? 0,
                'solucion_flocl_146' => $wasteProcess->solucion_flocl_146 ?? 0,
                'ph_inicial' => $wasteProcess->ph_inicial ?? 0,
                'ph_final' => $wasteProcess->ph_final ?? 0,
                'lodos_generados_porcentaje' => $wasteProcess->lodos_generados_porcentaje ?? 0,
                'lodos_generados_tnk' => $wasteProcess->lodos_generados_tnk_lodos ?? 0,
                'ingreso_filtro_prensa' => $wasteProcess->ingreso_filtro_prensa ?? 0,
                'lodos_generados_disposicion' => $wasteProcess->lodos_generados_disposicion ?? 0,
                'agua_retorno_proceso_filtro_prensa' => $wasteProcess->agua_retorno_proceso_filtro_prensa ?? 0,
                'ph_lodos' => $wasteProcess->ph_lodos ?? 0,
                'cantidad_total_agua_reciclada' => $wasteProcess->cantidad_total_agua_reciclada ?? 0,
                'others' => $wasteProcess->others ?? 0,
                'client_profile' => $wasteProcess->solution ?? '',
                'comments' => $wasteProcess->comments ?? '',
                'client' => $this->getClients($wasteProcess),
            ]
        ];
    }

    private function getIncineracionDetails(WasteProcess $wasteProcess, string $manifestFolios): array
    {

        return [[
            'waste_process_id' => $wasteProcess->waste_process_id ?? 0,
            'id' => $wasteProcess->id ?? 0,
            'manifest_detail_id' => $wasteProcess->manifest_detail_id ?? 0,
            'client_profile' => $wasteProcess->solution ?? '',
            'client' => $this->getClients($wasteProcess),
            'container' => 'Pendiente',
            'cantidad_ingresado' => $wasteProcess->cantidad_solucion_reciclar ?? 0,
            'temperatura_1' => $wasteProcess->temperatura_1 ?? 0,
            'temperatura_2' => $wasteProcess->temperatura_2 ?? 0,
            'temperatura_3' => $wasteProcess->temperatura_3 ?? 0,
            'cantidad_recuperados' => $wasteProcess->cantidad_recuperados ?? 0,
            'porcentaje_recuperado' => $wasteProcess->porcentaje_recuperado ?? 0,
            'cantidad_sedimentos' => $wasteProcess->cantidad_sedimentos ?? 0,
            'porcentaje_sedimentos' => $wasteProcess->porcentaje_sedimentos ?? 0,
            'factor_consumo_kw' => $wasteProcess->factor_consumo_kw ?? 0,
            'consumo_kw_hrs' => $wasteProcess->consumo_kw_hrs ?? 0,
            'costo_kw_hr' => $wasteProcess->costo_kw_hr ?? 0,
            'costo_total_operacion' => $wasteProcess->costo_total_operacion ?? 0,
            'others' => $wasteProcess->others ?? 0,
            'manifest_id' => $manifestFolios,
            'comments' => $wasteProcess->comments ?? '',
        ]];
    }
    private function getClients(WasteProcess $wasteProcess): string
    {
        $clients = $wasteProcess->wasteProcessDetails->map(function (WasteProcessDetail $detail) {
            return $detail->manifest->client->name;
        })->unique()->values()->implode(',');

        return $clients;
    }
}
