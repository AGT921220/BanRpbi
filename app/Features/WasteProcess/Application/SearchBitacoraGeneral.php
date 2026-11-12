<?php

namespace App\Features\WasteProcess\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\Service;
use App\Models\ServiceDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SearchBitacoraGeneral
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
    ) {}

    /**
     * @param  array<int, mixed>  $modifiers
     * @return array<string, mixed>
     */
    public function __invoke(
        array $modifiers,
        int $draw,
    ): array {
        $query = ServiceDetail::query()
            ->select([
                'service_details.service_id',
                'service_details.id as service_detail_id',
                'service_details.weight',
                'services.service_date',
                'services.status',
                'services.warehouse_entry_at',
                'services.collected_at',
                'clients.company as client_name',
                'manifests.id as manifest_id',
                DB::raw("TRIM(CONCAT(COALESCE(drivers.name, ''), ' ', COALESCE(drivers.parentarl_surname, ''), ' ', COALESCE(drivers.maternal_surname, ''))) as driver_name"),
                'rpbi_profiles.code as rpbi_profile',
                'rpbi_profiles.name as rpbi_profile_name',
            ])
            ->join('services', 'services.id', '=', 'service_details.service_id')
            ->leftJoin('rpbi_profiles', 'rpbi_profiles.id', '=', 'service_details.rpbi_profile_id')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->join('manifests', 'manifests.service_id', '=', 'services.id')
            ->leftJoin('drivers', 'drivers.id', '=', 'services.driver_id')
            ->whereNull('manifests.deleted_at');

            return $query->get()->toArray();
        
        
            $data = $this->builderFilter->paginate(
            builder: $query,
            modifiers: $modifiers,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (ServiceDetail $detail) {
            $driver = trim((string) $detail->driver_name);
            $entry = $detail->warehouse_entry_at;

            return [
                'service_id' => (int) $detail->service_id,
                'service_detail_id' => (int) $detail->service_detail_id,
                'weight' => $detail->weight ?? '',
                'service_date' => $detail->service_date
                    ? Carbon::parse($detail->service_date)->format('d/m/Y')
                    : '',
                'status' => Service::statusLabel((string) $detail->status),
                'collected_at' => $detail->collected_at
                    ? Carbon::parse($detail->collected_at)->format('d/m/Y H:i')
                    : '—',
                'warehouse_entry_at' => $entry
                    ? Carbon::parse($entry)->format('d/m/Y H:i')
                    : '—',
                'client_name' => $detail->client_name ?: '—',
                'manifest_id' => (int) $detail->manifest_id,
                'driver_name' => $driver !== '' ? $driver : '—',
                'rpbi_profile' => $detail->rpbi_profile ?: '—',
                'rpbi_profile_name' => $detail->rpbi_profile_name ?: '—',
            ];
        });

        return $data;
    }
}
