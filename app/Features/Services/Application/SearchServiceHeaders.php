<?php

namespace App\Features\Services\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\Service;

class SearchServiceHeaders
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
            builder: Service::select(
                'services.id',
                'services.status',
                'clients.company',
                'services.service_date',
                'zones.name as zone',
                'clients.maps_url as client_maps_url',
            )
                ->join('clients', 'services.client_id', '=', 'clients.id')
                ->join('zones', 'services.zone_id', '=', 'zones.id'),
            modifiers: $filters,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (Service $service) {
            return [
                'id' => $service->id,
                'status' => $this->resolveServiceStatus($service->status),
                'client' => $service->company,
                'zone' => $service->zone,
                'date' => $service->service_date,
                'client_maps_url' => $service->client_maps_url,
            ];
        });

        return $data;
    }

    private function resolveServiceStatus(string $status): string
    {
        return match ($status) {
            Service::STATUS_PENDING => 'Pendiente',
            default => 'Desconocido',
        };
    }
}