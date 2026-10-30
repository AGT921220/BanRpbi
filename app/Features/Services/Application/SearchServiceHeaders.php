<?php

namespace App\Features\Services\Application;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\RpbiProfiles\Domain\RpbiProfile;
use App\Features\Services\Domain\ServiceDetail as ServiceDetailDto;
use App\Features\Services\Domain\ServiceDetailsResponse;
use App\Features\Shared\Query\BuilderFilter;
use App\Models\Service;
use App\Models\ServiceDetail;
use Illuminate\Support\Facades\Gate;

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
                'services.invoice_id',
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

        $canView = Gate::allows(PermissionTypes::COLLECTIONS_VIEW);
        $canManifestPdf = Gate::allows(PermissionTypes::MANIFESTS_DOWNLOAD)
            || Gate::allows(PermissionTypes::MANIFESTS_VIEW);
        $canInvoicePdf = Gate::allows(PermissionTypes::INVOICES_DOWNLOAD_PDF)
            || Gate::allows(PermissionTypes::INVOICES_VIEW);

        $nextService = true;
        $data['data'] = $data['data']->map(function (Service $service) use (
            &$nextService,
            $canView,
            $canManifestPdf,
            $canInvoicePdf,
        ) {
            $domainServiceDetails = $service->serviceDetails->map(function (ServiceDetail $serviceDetail) {
                $rpbiProfile = $serviceDetail->rpbiProfile;

                return new ServiceDetailDto(
                    $serviceDetail->id,
                    $serviceDetail->weight,
                    $rpbiProfile
                        ? new RpbiProfile(
                            $rpbiProfile->id,
                            $rpbiProfile->code,
                            $rpbiProfile->name,
                        )
                        : null,
                );
            });
            $statusMobile = $nextService && $service->status !== Service::STATUS_COLLECTED
                ? Service::STATUS_NEXT
                : $service->status;
            $nextService = $service->status !== Service::STATUS_COLLECTED ? false : true;

            return [
                'id' => $service->id,
                'status' => $this->resolveServiceStatus($service->status),
                'status_mobile'=> $this->resolveServiceStatus($statusMobile),
                'client_name' => $service->company,
                'zone' => $service->zone,
                'date' => $service->service_date,
                'client_maps_url' => $service->client_maps_url,
                'driver_id' => $service->driver_id,
                'driver_name' => $service->driver_id === null
                    ? 'Sin Chofer Asignado'
                    : $service->driver_name,
                'manifest_id' => $service->manifest_id,
                'invoice_id' => $service->invoice_id,
                'can_view' => $canView,
                'can_manifest_pdf' => $canManifestPdf && filled($service->manifest_id),
                'can_invoice_pdf' => $canInvoicePdf && filled($service->invoice_id),
                'service_details' => (new ServiceDetailsResponse(count($domainServiceDetails), ...$domainServiceDetails))->toArray(),
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
