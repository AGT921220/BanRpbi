<?php

namespace App\Features\WasteProcess\UseCases;

use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use Illuminate\Support\Collection;

class CreateWasteProcess
{

    public function __invoke(
        Collection $manifests,
        int $wasteProcessTypeId,
        string $dateStart,
        int $userId
    ): int {
        $totalQuantity = $this->getTotalQuantity($manifests);
        $wasteProcessId = $this->create($wasteProcessTypeId, $dateStart, $userId, $totalQuantity);

        $this->createDetails($manifests, $wasteProcessId);
        return $wasteProcessId;
    }
    private function create(
        int $wasteProcessTypeId,
        string $dateStart,
        int $userId,
        string $totalQuantity
    ): int {

        $wasteProcess = new WasteProcess();
        $wasteProcess->waste_process_type_id = $wasteProcessTypeId;
        $wasteProcess->date_start = $dateStart;
        $wasteProcess->user_id = $userId;
        $wasteProcess->cantidad_solucion_reciclar = $totalQuantity;
        $wasteProcess->save();

        return $wasteProcess->id;
    }
    private function createDetails(Collection $manifests, int $wasteProcessId): void
    {
        $detailIds = $manifests->flatMap(function ($manifest) {
            return $manifest->service?->serviceDetails?->pluck('id') ?? collect();
        });
        $completedIds = WasteProcessDetail::query()
            ->where('is_completed', true)
            ->whereIn('service_detail_id', $detailIds)
            ->pluck('service_detail_id');

        $manifests->each(function ($manifest) use ($wasteProcessId, $completedIds) {
            $details = $manifest->service?->serviceDetails ?? collect();
            $details->each(function ($detail) use ($wasteProcessId, $manifest, $completedIds) {
                if ($completedIds->contains($detail->id)) {
                    return;
                }

                $wasteProcessDetail = new WasteProcessDetail();
                $wasteProcessDetail->waste_process_id = $wasteProcessId;
                $wasteProcessDetail->manifest_id = $manifest->id;
                $wasteProcessDetail->service_detail_id = $detail->id;
                $wasteProcessDetail->total_weight = is_numeric($detail->weight) ? $detail->weight : '0';
                $wasteProcessDetail->process_weight = '0';
                $wasteProcessDetail->save();
            });
        });
    }

    private function getTotalQuantity(Collection $manifests): string
    {
        $total = $manifests->sum(function ($manifest) {
            $details = $manifest->service?->serviceDetails ?? collect();

            return $details->sum(function ($detail) {
                if (! is_numeric($detail->weight)) {
                    return 0;
                }

                return (float) $detail->weight;
            });
        });

        return number_format($total, 2, '.', '');
    }
}
