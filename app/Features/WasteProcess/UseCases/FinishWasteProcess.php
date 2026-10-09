<?php

namespace App\Features\WasteProcess\UseCases;

use App\Models\WasteProcess;
use App\Models\WasteProcessDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinishWasteProcess
{
    public function __invoke(int $wasteProcessId, Collection $details)
    {
        $userId = auth()->id();
        $finishedAt = Carbon::now()->toDateTimeString();
        $this->saveDetails($details, $userId, $finishedAt);
        $this->saveHeader($wasteProcessId, $userId, $finishedAt);
    }
    private function saveDetails(Collection $details, int $userId, string $finishedAt)
    {
        $details->each(function ($detail) use ($userId, $finishedAt) {
            $wasteProcessDetail = WasteProcessDetail::find($detail->id);
            $isCompleted = $this->isCompleted(
                // $wasteProcessDetail->waste_process_id,
                $wasteProcessDetail->manifest_id,
                $wasteProcessDetail->manifest_detail_id,
                $wasteProcessDetail->total_weight,
                $detail->value
            );
            $wasteProcessDetail->is_finished = 1;
            $wasteProcessDetail->is_completed = $isCompleted ?
                WasteProcessDetail::IS_COMPLETED : WasteProcessDetail::IS_NOT_COMPLETED;
            $wasteProcessDetail->finished_by_id = $userId;
            $wasteProcessDetail->finished_at = $finishedAt;
            $wasteProcessDetail->process_weight = $detail->value;
            $wasteProcessDetail->save();
            if ($isCompleted) {
                WasteProcessDetail::where('manifest_id', $wasteProcessDetail->manifest_id)
                    ->where('manifest_detail_id', $wasteProcessDetail->manifest_detail_id)
                    ->update(['is_completed' => WasteProcessDetail::IS_COMPLETED]);
            }
        });
    }
    private function saveHeader(int $wasteProcessId, int $userId, string $finishedAt)
    {
        $wasteProcess = WasteProcess::find($wasteProcessId);
        $wasteProcess->is_finished = 1;
        $wasteProcess->finished_by_id = $userId;
        $wasteProcess->finished_at = $finishedAt;
        $wasteProcess->save();
    }
    private function isCompleted(
        int $manifestId,
        int $manifestDetailId,
        string $totalWeight,
        string $totalCaptured
    ): bool {
        if ($totalWeight == $totalCaptured) {
            return true;
        }

        $sumWeight = WasteProcessDetail::
            where('manifest_id', $manifestId)
            ->where('manifest_detail_id', $manifestDetailId)
            ->sum('process_weight') + $totalCaptured;
        return $sumWeight == $totalWeight;
    }
}
