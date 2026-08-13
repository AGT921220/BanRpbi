<?php

namespace App\Features\Manifests\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\Manifest;

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
            builder: Manifest::select('manifests.id', 'manifests.status', 'clients.company',
            'service_date')
            ->join('services', 'manifests.service_id', '=', 'services.id')
            ->join('clients', 'services.client_id', '=', 'clients.id'),
            modifiers: $filters,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (Manifest $manifest) {
            return
                [
                    'id' => $manifest->id,
                    'status' => $this->resolveManifestStatus($manifest->status),
                    'client' => $manifest->company,
                    'date'  => $manifest->service_date,
                ];
        });
        return $data;
    }
    private function resolveManifestStatus(string $status): string
    {
        return match ($status) {
            Manifest::STATUS_PENDING => 'Pendiente',
            default => 'Desconocido',
        };
    }
}
