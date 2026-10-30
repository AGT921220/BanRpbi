<?php

namespace App\Features\WasteProcess\UseCases;

use App\Features\WasteProcess\Helpers\ServiceDetailBalance;
use App\Models\ProcessCertificate;
use App\Models\ProcessFolio;
use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinishWasteProcess
{
    public function __construct(
        private readonly ServiceDetailBalance $balance,
    ) {}

    /**
     * @param  Collection<int, object{id:int,value:float|string}>  $details
     */
    public function __invoke(int $wasteProcessId, Collection $details, int $userId): void
    {
        DB::transaction(function () use ($wasteProcessId, $details, $userId): void {
            $process = WasteProcess::query()
                ->whereKey($wasteProcessId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($process->is_finished) {
                throw ValidationException::withMessages([
                    'process' => 'El proceso ya está cerrado.',
                ]);
            }

            $finishedAt = Carbon::now()->toDateTimeString();
            $detailIds = $details->pluck('id')->map(fn ($id) => (int) $id)->all();

            $processDetails = WasteProcessDetail::query()
                ->where('waste_process_id', $wasteProcessId)
                ->whereIn('id', $detailIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($processDetails->count() !== count($detailIds)) {
                throw ValidationException::withMessages([
                    'details' => 'Hay líneas de proceso inválidas o ajenas a este proceso.',
                ]);
            }

            foreach ($details as $capture) {
                $detail = $processDetails->get((int) $capture->id);
                $weight = round((float) $capture->value, 2);

                if ($weight <= 0) {
                    throw ValidationException::withMessages([
                        'details' => 'El peso capturado debe ser mayor a cero.',
                    ]);
                }

                $remaining = $this->balance->remainingWeight(
                    (int) $detail->service_detail_id,
                    (int) $detail->id,
                );

                if ($weight > $remaining) {
                    throw ValidationException::withMessages([
                        'details' => "El peso capturado ({$weight}) excede el remanente disponible ({$remaining}).",
                    ]);
                }

                $treatedAfter = round(
                    $this->balance->treatedWeight((int) $detail->service_detail_id, (int) $detail->id) + $weight,
                    2,
                );
                $available = $this->balance->availableWeight((int) $detail->service_detail_id);
                $isCompleted = $treatedAfter >= $available;

                $detail->process_weight = number_format($weight, 2, '.', '');
                $detail->is_finished = 1;
                $detail->is_completed = $isCompleted
                    ? WasteProcessDetail::IS_COMPLETED
                    : WasteProcessDetail::IS_NOT_COMPLETED;
                $detail->finished_by_id = $userId;
                $detail->finished_at = $finishedAt;
                $detail->save();

                if ($isCompleted) {
                    WasteProcessDetail::query()
                        ->where('service_detail_id', $detail->service_detail_id)
                        ->update(['is_completed' => WasteProcessDetail::IS_COMPLETED]);
                }
            }

            $process->is_finished = 1;
            $process->finished_by_id = $userId;
            $process->finished_at = $finishedAt;
            $process->save();

            $this->createCertificates($process);
        });
    }

    private function createCertificates(WasteProcess $process): void
    {
        $rows = WasteProcessDetail::query()
            ->where('waste_process_id', $process->id)
            ->with(['manifest.service'])
            ->get()
            ->groupBy(fn (WasteProcessDetail $detail) => $detail->manifest?->service?->client_id);

        foreach ($rows as $clientId => $details) {
            if (! $clientId) {
                continue;
            }

            $manifestIds = $details->pluck('manifest_id')->unique()->values();

            $certificate = ProcessCertificate::query()->create([
                'waste_process_id' => $process->id,
                'client_id' => (int) $clientId,
                'manifest_id' => (int) $manifestIds->first(),
                'manifest_ids' => $manifestIds->implode(','),
                'manifest_folios' => $manifestIds->implode(','),
                'date' => Carbon::now()->toDateString(),
            ]);

            ProcessFolio::query()->create([
                'client_id' => (int) $clientId,
                'process_certificate_id' => $certificate->id,
                'waste_process_id' => $process->id,
            ]);
        }
    }
}
