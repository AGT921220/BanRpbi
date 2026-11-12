<?php

namespace App\Features\WasteProcess\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\ServiceDetail;
use App\Models\WasteProcess;
use App\Models\WasteProcessType;
use Illuminate\Support\Facades\DB;

class SearchBitacoraGeneral
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
    ) {}

    public function __invoke(
        array $options,
        int $draw,
    ) {

        $query = ServiceDetail::select(
                'service_details.service_id',
                'service_details.id as service_detail_id',
                'service_details.weight',
                'services.service_date',
                'services.status',
                'services.warehouse_entry_at',
                'clients.company as client_name',
                'manifests.id as manifest_id',
                DB::raw('CONCAT(drivers.name, " ", drivers.parentarl_surname) as driver_name'),
            )
            ->join('rpbi_profiles', 'rpbi_profiles.id', '=', 'service_details.rpbi_profile_id')
            ->join('services', 'services.id', '=', 'service_details.service_id')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->join('manifests', 'manifests.service_id', '=', 'services.id')
            ->leftJoin('drivers', 'drivers.id', '=', 'services.driver_id')
            //limit(100)->
            ;


        // return $query;
        $data = $this->builderFilter->paginate(
            builder: $query,
            modifiers: $options,
            draw: $draw,
        );

        return $data;
        $data['data'] = $data['data']->map(function (WasteProcess $process) {
            $manifests = $process->details
                ->pluck('manifest_id')
                ->unique()
                ->filter()
                ->implode(', ');

            return [
                'id' => $process->id,
                'fecha' => $process->date_start?->format('d/m/Y') ?? '',
                'tipo' => $process->type_name ?: '—',
                'estado' => $process->is_finished ? 'Cerrado' : 'Tratamiento',
                'manifiestos' => $manifests !== '' ? $manifests : '—',
            ];
        });

        return $data;
    }

    /**
     * @return array<int, int>
     */
    private function typeIds(string $filtro): array
    {
        if ($filtro === 'incineracion') {
            return [WasteProcessType::PROCESS_INCINERACION];
        }

        if ($filtro === 'esterilizacion') {
            return [WasteProcessType::PROCESS_ESTERILIZACION];
        }

        return [
            WasteProcessType::PROCESS_INCINERACION,
            WasteProcessType::PROCESS_ESTERILIZACION,
        ];
    }
}
