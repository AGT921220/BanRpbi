<?php

namespace App\Features\WasteProcess\UseCases;

use App\Features\WasteProcess\Helpers\ServiceDetailBalance;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateWasteProcess
{
    public function __construct(
        private readonly ServiceDetailBalance $balance,
    ) {}

    /**
     * @param  Collection<int, Manifest>|list<int>  $manifestsOrServiceDetailIds
     */
    public function __invoke(
        Collection $manifests,
        int $wasteProcessTypeId,
        string $dateStart,
        int $userId,
        ?array $serviceDetailIds = null,
    ): int {
        return (int) DB::transaction(function () use (
            $manifests,
            $wasteProcessTypeId,
            $dateStart,
            $userId,
            $serviceDetailIds,
        ): int {
            $lines = $this->resolveEligibleLines($manifests, $serviceDetailIds);

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'manifest_ids' => 'No hay líneas elegibles con remanente para procesar.',
                ]);
            }

            $totalQuantity = number_format(
                $lines->sum(fn (array $line) => $line['remaining']),
                2,
                '.',
                '',
            );

            $wasteProcess = new WasteProcess();
            $wasteProcess->waste_process_type_id = $wasteProcessTypeId;
            $wasteProcess->date_start = $dateStart;
            $wasteProcess->user_id = $userId;
            $wasteProcess->cantidad_solucion_reciclar = $totalQuantity;
            $wasteProcess->save();

            foreach ($lines as $line) {
                WasteProcessDetail::query()->create([
                    'waste_process_id' => $wasteProcess->id,
                    'manifest_id' => $line['manifest_id'],
                    'service_detail_id' => $line['service_detail_id'],
                    'total_weight' => number_format($line['remaining'], 2, '.', ''),
                    'process_weight' => '0',
                ]);
            }

            return (int) $wasteProcess->id;
        });
    }

    /**
     * @param  Collection<int, Manifest>  $manifests
     * @param  list<int>|null  $serviceDetailIds
     * @return Collection<int, array{manifest_id:int,service_detail_id:int,remaining:float}>
     */
    private function resolveEligibleLines(Collection $manifests, ?array $serviceDetailIds): Collection
    {
        $selected = $serviceDetailIds
            ? collect($serviceDetailIds)->map(fn ($id) => (int) $id)->all()
            : null;

        $lines = collect();

        foreach ($manifests as $manifest) {
            $service = $manifest->service;

            if (! $service || $service->status !== Service::STATUS_COLLECTED) {
                continue;
            }

            if ($service->warehouse_entry_at === null) {
                throw ValidationException::withMessages([
                    'manifest_ids' => 'El manifiesto '.$manifest->id.' no tiene entrada a almacén.',
                ]);
            }

            $details = ServiceDetail::query()
                ->where('service_id', $service->id)
                ->when($selected, fn ($q) => $q->whereIn('id', $selected))
                ->lockForUpdate()
                ->get();

            foreach ($details as $detail) {
                $openInOther = WasteProcessDetail::query()
                    ->where('service_detail_id', $detail->id)
                    ->where('is_finished', 0)
                    ->whereHas('process', fn ($q) => $q->where('is_finished', 0))
                    ->exists();

                if ($openInOther) {
                    continue;
                }

                $remaining = $this->balance->remainingWeight((int) $detail->id);

                if ($remaining <= 0) {
                    continue;
                }

                $lines->push([
                    'manifest_id' => (int) $manifest->id,
                    'service_detail_id' => (int) $detail->id,
                    'remaining' => $remaining,
                ]);
            }
        }

        return $lines;
    }
}
