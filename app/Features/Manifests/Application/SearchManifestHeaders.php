<?php

namespace App\Features\Manifests\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\Manifest;
use App\Models\Service;

class SearchManifestHeaders
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
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
                    'services.status',
                    'clients.company',
                    'services.service_date',
                    'services.invoice_id',
                    'invoices.id as invoice',
                ])
                ->join('services', 'manifests.service_id', '=', 'services.id')
                ->join('clients', 'services.client_id', '=', 'clients.id')
                ->leftJoin('invoices', 'invoices.id', '=', 'services.invoice_id'),
            modifiers: $filters,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (Manifest $manifest) {
            return [
                'id' => $manifest->id,
                'status' => $this->resolveManifestStatus($manifest->status),
                'client' => $manifest->company,
                'date' => $manifest->service_date,
                'invoice_id' => $manifest->invoice ?? $manifest->invoice_id,
            ];
        });

        return $data;
    }

    private function resolveManifestStatus(string $status): string
    {
        return match ($status) {
            Service::STATUS_PENDING => 'Pendiente',
            Service::STATUS_SCHEDULED => 'Programado',
            Service::STATUS_COLLECTED => 'Recolectado',
            // Service::STATUS_IN_WAREHOUSE => 'En almacén',
            Service::STATUS_NEXT => 'Siguiente',
            default => 'Desconocido',
        };
    }
}
