<?php

namespace App\Features\Manifests\Application;

use App\Features\Shared\Application\Manifest\ManifestStatusCalculator;
use App\Features\Shared\Query\BuilderFilter;
use App\Models\Manifest;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class SearchManifestHeaders
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
        private readonly ManifestStatusCalculator $manifestStatusCalculator,
    ) {}

    /**
     * @param  array<int, mixed>  $filters
     * @return array<string, mixed>
     */
    public function __invoke(array $filters = [], int $draw = 1): array
    {
        $data = $this->builderFilter->paginate(
            builder: Manifest::query()
                ->select([
                    'manifests.id',
                    'services.id as service_id',
                    'services.status',
                    'clients.company',
                    'services.service_date',
                    'services.invoice_id',
                    'invoices.id as invoice',
                    'services.warehouse_entry_at as hasWarehouseEntry',
                    DB::raw('(SELECT EXISTS(SELECT 1 FROM waste_process_details WHERE manifest_id = manifests.id)) AS hasProcess'),
                    DB::raw('(
    EXISTS(
        SELECT 1
        FROM waste_process_details
        WHERE manifest_id = manifests.id
    )
    AND NOT EXISTS(
        SELECT 1
        FROM waste_process_details
        WHERE manifest_id = manifests.id
          AND (is_finished <> 1 OR is_finished IS NULL)
    )
) AS hasCompleteProcess'),
                ])
                ->join('services', 'manifests.service_id', '=', 'services.id')
                ->join('clients', 'services.client_id', '=', 'clients.id')
                ->leftJoin('invoices', 'invoices.id', '=', 'services.invoice_id')
                // ->where('manifests.id', 10)
                ,
            modifiers: $filters,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (Manifest $manifest) {
            return [
                'id' => $manifest->id,
                'service_id' => $manifest->service_id,
                'status' => $this->manifestStatusCalculator->__invoke((string) $manifest->status, $manifest->hasWarehouseEntry, $manifest->hasProcess, $manifest->hasCompleteProcess),
                'client' => $manifest->company,
                'date' => $manifest->service_date,
                'invoice_id' => $manifest->invoice ?? $manifest->invoice_id,
            ];
        });

        return $data;
    }
}
