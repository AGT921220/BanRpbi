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
     * @param  array<int, mixed>  $options
     * @return array<string, mixed>
     */
    public function __invoke(
        string $dateFrom,
        string $dateTo,
        string $dateField,
        ?int $clientId,
        string $status,
        ?int $manifestId,
        ?int $driverId,
        ?int $profileId,
        array $options,
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

        $dateColumn = $this->dateColumn($dateField);

        if ($dateFrom !== '') {
            $query->whereDate($dateColumn, '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate($dateColumn, '<=', $dateTo);
        }

        if ($clientId !== null) {
            $query->where('services.client_id', $clientId);
        }

        if ($status !== '') {
            $query->where('services.status', $status);
        }

        if ($manifestId !== null) {
            $query->where('manifests.id', $manifestId);
        }

        if ($driverId !== null) {
            $query->where('services.driver_id', $driverId);
        }

        if ($profileId !== null) {
            $query->where('service_details.rpbi_profile_id', $profileId);
        }

        $data = $this->builderFilter->paginate(
            builder: $query,
            modifiers: $options,
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

    private function dateColumn(string $dateField): string
    {
        if ($dateField === 'collected_at') {
            return 'services.collected_at';
        }

        if ($dateField === 'warehouse_entry_at') {
            return 'services.warehouse_entry_at';
        }

        return 'services.service_date';
    }
}
