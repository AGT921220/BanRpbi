<?php

namespace App\Features\WasteProcess\Helpers;

use App\Features\WasteProcess\Dto\WasteProcess as DtoWasteProcess;
use App\Features\WasteProcess\Dto\WasteProcessDate;
use App\Features\WasteProcess\Dto\WasteProcessFinish;
use App\Features\WasteProcess\Dto\WasteProcessHeader;
use App\Features\WasteProcess\Dto\WasteProcessMachine;
use App\Models\Manifest;
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
            $wasteProcess->hour_start_machine
                ? Carbon::parse($wasteProcess->hour_start_machine)->format('H:i')
                : null,
            $wasteProcess->hour_machine
                ? Carbon::parse($wasteProcess->hour_machine)->format('H:i')
                : null,
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
        $manifestFolios = Manifest::query()->whereIn('id', $manifestIds)->pluck('id')->implode(',');

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
            $captured = (float) ($detail->total_captured_weight ?? 0);
            $total = (float) $detail->total_weight;

            return [
                'id' => $detail->id,
                'manifest_id' => $detail->manifest_id,
                'service_detail_id' => $detail->service_detail_id,
                'total_weight' => $detail->total_weight,
                'process_weight' => $detail->process_weight,
                'captured_weight' => number_format($captured, 2, '.', ''),
                'remaing_weight' => number_format(max($total - $captured, 0), 2, '.', ''),
                'is_finished' => (bool) $detail->is_finished,
                'is_completed' => (bool) $detail->is_completed,
                'profile' => $detail->serviceDetail?->rpbiProfile?->name,
                'client' => $detail->manifest?->service?->client?->company
                    ?: trim(($detail->manifest?->service?->client?->name ?? '').' '.($detail->manifest?->service?->client?->parentarl_surname ?? '')),
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
            'temp_camara_primaria' => $wasteProcess->temp_camara_primaria,
            'temp_camara_secundaria' => $wasteProcess->temp_camara_secundaria,
            'temp_entrada_venturi' => $wasteProcess->temp_entrada_venturi,
            'temp_salida_venturi' => $wasteProcess->temp_salida_venturi,
            'temp_salida_chimenea' => $wasteProcess->temp_salida_chimenea,
            'oxigeno' => $wasteProcess->oxigeno,
            'presion_negativa' => $wasteProcess->presion_negativa,
            'consumo_gas_lp' => $wasteProcess->consumo_gas_lp,
            'consumo_promedio_agua' => $wasteProcess->consumo_promedio_agua,
            'evaporacion_agua_tratada' => $wasteProcess->evaporacion_agua_tratada,
            'patologicos' => $wasteProcess->patologicos,
            'cultivos_cepas' => $wasteProcess->cultivos_cepas,
            'sangre' => $wasteProcess->sangre,
            'ceniza_generada' => $wasteProcess->ceniza_generada,
            'descarga_lavador' => $wasteProcess->descarga_lavador,
            'others' => $wasteProcess->others ?? 0,
            'manifest_id' => $manifestFolios,
            'comments' => $wasteProcess->comments ?? '',
        ]];
    }
    private function getClients(WasteProcess $wasteProcess): string
    {
        return $wasteProcess->wasteProcessDetails
            ->map(function (WasteProcessDetail $detail) {
                $client = $detail->manifest?->service?->client;

                return $client?->company
                    ?: trim(($client?->name ?? '').' '.($client?->parentarl_surname ?? ''));
            })
            ->filter()
            ->unique()
            ->values()
            ->implode(',');
    }
}
