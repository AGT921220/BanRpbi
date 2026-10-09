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
        int $destinationId,
        string $dateStart,
        int $userId
    ): int {
        $totalQuantity = $this->getTotalQuantity($manifests);
        $wasteProcessId = $this->create($wasteProcessTypeId, $destinationId, $dateStart, $userId, $totalQuantity);

        $this->createDetails($manifests, $wasteProcessId, $wasteProcessTypeId);
        return $wasteProcessId;
    }
    private function create(
        int $wasteProcessTypeId,
        int $destinationId,
        string $dateStart,
        int $userId,
        string $totalQuantity
    ): int {

        $wasteProcess = new WasteProcess();
        $wasteProcess->waste_process_type_id = $wasteProcessTypeId;
        $wasteProcess->destination_id = $destinationId;
        $wasteProcess->date_start = $dateStart;
        $wasteProcess->user_id = $userId;
        $wasteProcess->cantidad_solucion_reciclar = $totalQuantity;
        $wasteProcess->save();

        return $wasteProcess->id;
    }
    private function createDetails(Collection $manifests, int $wasteProcessId): void
    {
        $manifests->each(function ($manifest) use ($wasteProcessId) {
            $manifestId = $manifest->id;
            $manifest->manifestDetails->each(function ($manifestDetail) use ($wasteProcessId, $manifestId) {
                $wasteProcessDetail = new WasteProcessDetail();
                $wasteProcessDetail->waste_process_id = $wasteProcessId;
                $wasteProcessDetail->manifest_id = $manifestId;
                $wasteProcessDetail->manifest_detail_id = $manifestDetail->id;

                // $weight = $manifestDetail->serviceDetail->weight_new??$manifestDetail->serviceDetail->weight;
                // $wasteProcessDetail->total_weight = $manifestDetail->serviceDetail->quantity*$weight;
                // $wasteProcessDetail->total_weight = $manifestDetail->serviceDetail->quantity;
                $wasteProcessDetail->total_weight = $manifestDetail
                ->serviceDetail->weight_new??$manifestDetail->serviceDetail->weight;
                $wasteProcessDetail->process_weight = 0;
                $wasteProcessDetail->save();
            });
        });
    }
    private function getTotalQuantity(Collection $manifests): string
    {
        $totalQuantity = '0';
        $manifests->each(function ($manifest) use (&$totalQuantity) {
            $manifest->manifestDetails->each(function ($manifestDetail) use (&$totalQuantity) {
                $weight = $manifestDetail->serviceDetail->weight_new??$manifestDetail->serviceDetail->weight;
                // $quantity = $manifestDetail->serviceDetail->quantity*$weight;

                $totalQuantity = bcadd($totalQuantity, $weight, 2);
            });
        });
        return $totalQuantity;
    }
}
