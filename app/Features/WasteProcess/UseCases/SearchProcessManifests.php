<?php

namespace App\Features\WasteProcess\UseCases;

use App\Features\WasteProcess\Helpers\ServiceDetailBalance;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\WasteProcessDetail;
use Illuminate\Support\Collection;

class SearchProcessManifests
{
    public function __construct(
        private readonly ServiceDetailBalance $balance,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function __invoke(): Collection
    {
        return Manifest::query()
            ->select([
                'manifests.id',
                'manifests.service_id',
                'services.status',
                'services.service_date',
                'clients.company',
            ])
            ->join('services', 'services.id', '=', 'manifests.service_id')
            ->join('clients', 'clients.id', '=', 'services.client_id')
            ->whereNull('manifests.deleted_at')
            ->where('services.status', Service::STATUS_COLLECTED)
            ->whereNotNull('services.warehouse_entry_at')
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
                $lines = $details
                    ->map(function (ServiceDetail $detail) use ($manifest) {
                        $openInOther = WasteProcessDetail::query()
                            ->where('service_detail_id', $detail->id)
                            ->where('is_finished', 0)
                            ->whereHas('process', fn ($q) => $q->where('is_finished', 0))
                            ->exists();

                        $remaining = $this->balance->remainingWeight((int) $detail->id);

                        if ($remaining <= 0 || $openInOther) {
                            return null;
                        }

                        return [
                            'service_detail_id' => $detail->id,
                            'profile' => $detail->rpbiProfile?->name,
                            'code' => $detail->rpbiProfile?->code,
                            'available' => $this->balance->availableWeight((int) $detail->id),
                            'remaining' => $remaining,
                            'manifest_id' => $manifest->id,
                        ];
                    })
                    ->filter()
                    ->values();

                if ($lines->isEmpty()) {
                    return null;
                }

                return [
                    'id' => $manifest->id,
                    'client' => (string) $manifest->company,
                    'date' => (string) $manifest->service_date,
                    'status' => (string) ($manifest->service?->status ?? $manifest->status ?? ''),
                    'profiles' => $lines->pluck('profile')->filter()->implode(', '),
                    'weight' => number_format($lines->sum('remaining'), 2, '.', ''),
                    'lines' => $lines->all(),
                ];
            })
            ->filter()
            ->values();
    }
}
