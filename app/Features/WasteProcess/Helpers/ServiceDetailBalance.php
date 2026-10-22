<?php

namespace App\Features\WasteProcess\Helpers;

use App\Models\ServiceDetail;
use App\Models\WasteProcessDetail;

final class ServiceDetailBalance
{
    public function availableWeight(int $serviceDetailId): float
    {
        $detail = ServiceDetail::query()->find($serviceDetailId);
        $available = is_numeric($detail?->weight) ? (float) $detail->weight : 0.0;

        return round(max($available, 0), 2);
    }

    public function treatedWeight(int $serviceDetailId, ?int $excludeDetailId = null): float
    {
        $query = WasteProcessDetail::query()
            ->where('service_detail_id', $serviceDetailId)
            ->where('is_finished', 1);

        if ($excludeDetailId !== null) {
            $query->whereKeyNot($excludeDetailId);
        }

        $sum = (float) $query->sum('process_weight');

        return round($sum, 2);
    }

    public function remainingWeight(int $serviceDetailId, ?int $excludeDetailId = null): float
    {
        return round(
            max(
                $this->availableWeight($serviceDetailId) - $this->treatedWeight($serviceDetailId, $excludeDetailId),
                0,
            ),
            2,
        );
    }

    public function isExhausted(int $serviceDetailId, ?int $excludeDetailId = null): bool
    {
        return $this->remainingWeight($serviceDetailId, $excludeDetailId) <= 0.0;
    }
}
