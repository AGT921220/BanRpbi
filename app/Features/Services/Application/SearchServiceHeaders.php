<?php

namespace App\Features\Services\Application;

use App\Features\RpbiProfiles\Domain\RpbiProfile;
use App\Features\RpbiProfiles\Domain\RpbiProfileResponse;
use App\Features\RpbiProfiles\Domain\RpbiProfilesResponse;
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
                'drivers.id as driver_id',
                'drivers.name as driver_name',
                'manifests.id as manifest_id',
            )
                ->join('clients', 'services.client_id', '=', 'clients.id')
                ->join('zones', 'services.zone_id', '=', 'zones.id')
                ->leftJoin('drivers', 'services.driver_id', '=', 'drivers.id')
                ->leftJoin('manifests', 'services.id', '=', 'manifests.service_id')
                ->with(['serviceDetails' => function ($query) {
                    $query->with('rpbiProfile');
                }]),
            modifiers: $filters,
            draw: $draw,
        );

        $nextService = true;
        $data['data'] = $data['data']->map(function (Service $service) use (&$nextService) {
            $rpbiProfiles = $service->serviceDetails->map(function ($serviceDetail) {
                return $serviceDetail->rpbiProfile;
            })->filter();
            $domainRpbiProfiles = $rpbiProfiles->map(function ($rpbiProfile) {
                return new RpbiProfile(
                    $rpbiProfile->id,
                    $rpbiProfile->code,
                    $rpbiProfile->name,
                );
            });
            $status = $nextService && $service->status !== Service::STATUS_COLLECTED ? Service::STATUS_NEXT : $service->status;
            $nextService = $service->status !== Service::STATUS_COLLECTED ? false : true;
            return [
                'id' => $service->id,
                'status' => $this->resolveServiceStatus($status),
                'client_name' => $service->company,
                'zone' => $service->zone,
                'date' => $service->service_date,
                'client_maps_url' => $service->client_maps_url,
                'driver_id' => $service->driver_id,
                'driver_name' => $service->driver_name,
                'manifest_id' => $service->manifest_id,
                'rpbi_profiles' => (new RpbiProfilesResponse(count($rpbiProfiles), ...$domainRpbiProfiles))->toArray()
            ];
        });

        return $data;
    }

    // private function resolveServiceStatus(string $status): string
    // {
    //     return match ($status) {
    //         Service::STATUS_PENDING => 'Pendiente',
    //         default => 'Desconocido',
    //     };
    // }
    private function resolveServiceStatus(string $status): array
    {
        return match ($status) {
            Service::STATUS_SCHEDULED => [
                'status' => Service::STATUS_SCHEDULED,
                'label' => 'Programado',
                'bg' => '#E0F2FE',
                'color' => '#0284C7',
                'icon' => 'calendar-outline',
            ],
            Service::STATUS_PENDING => [
                'status' => Service::STATUS_PENDING,
                'label' => 'Pendiente',
                'bg' => '#F1F5F9',
                'color' => '#64748B',
                'icon' => 'time-outline',
            ],

            Service::STATUS_NEXT => [
                'status' => Service::STATUS_NEXT,
                'label' => 'Siguiente',
                'bg' => '#FEF3C7',
                'color' => '#D97706',
                'icon' => 'play-circle-outline',
            ],

            Service::STATUS_COLLECTED => [
                'status' => Service::STATUS_COLLECTED,
                'label' => 'Recolectado',
                'bg' => '#D1FAE5',
                'color' => '#059669',
                'icon' => 'checkmark-circle-outline',
            ],

            default => [
                'status' => $status,
                'label' => 'Desconocido',
                'bg' => '#F1F5F9',
                'color' => '#64748B',
                'icon' => 'help-circle-outline',
            ],
        };
    }
}
