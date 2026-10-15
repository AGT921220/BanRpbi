<?php

namespace App\Features\WasteProcess\UseCases;

use App\Models\Manifest;
use App\Models\ServiceDetail;
use Illuminate\Support\Collection;

class SearchProcessManifests
{
    /**
     * @return Collection<int, array{id: int, client: string, date: string, profiles: string, weight: string}>
     */
    public function __invoke(): Collection
    {
        return Manifest::query()
            ->select([
                'manifests.id',
                'manifests.service_id',
                'services.service_date',
                'clients.company',
            ])
            ->join('services', 'services.id', '=', 'manifests.service_id')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->whereNull('manifests.deleted_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('service_details')
                    ->whereColumn('service_details.service_id', 'services.id')
                    ->whereNotExists(function ($completed) {
                        $completed->selectRaw('1')
                            ->from('waste_process_details')
                            ->whereColumn('waste_process_details.service_detail_id', 'service_details.id')
                            ->where('waste_process_details.is_completed', 1);
                    });
            })
            ->with(['service.serviceDetails.rpbiProfile'])
            ->orderByDesc('manifests.id')
            ->get()
            ->map(function (Manifest $manifest) {
                $details = $manifest->service?->serviceDetails ?? collect();

                return [
                    'id' => $manifest->id,
                    'client' => (string) $manifest->company,
                    'date' => (string) $manifest->service_date,
                    'profiles' => $details
                        ->map(fn (ServiceDetail $detail) => $detail->rpbiProfile?->name)
                        ->filter()
                        ->implode(', '),
                    'weight' => number_format($this->weightOf($details), 2, '.', ''),
                ];
            });
    }

    /**
     * @param  Collection<int, ServiceDetail>  $details
     */
    private function weightOf(Collection $details): float
    {
        return $details->sum(function (ServiceDetail $detail) {
            if (! is_numeric($detail->weight)) {
                return 0;
            }

            return (float) $detail->weight;
        });
    }
}
