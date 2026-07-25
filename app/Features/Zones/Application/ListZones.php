<?php

namespace App\Features\Zones\Application;

use App\Models\Zone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ListZones
{
    public function __invoke(
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return Zone::query()
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Zone>
     */
    public function forMapOverlay(?int $excludeZoneId = null): Collection
    {
        return Zone::query()
            ->select(['id', 'name', 'color', 'geometry'])
            ->where('is_active', true)
            ->when(
                $excludeZoneId !== null,
                fn ($query) => $query->where('id', '!=', $excludeZoneId)
            )
            ->orderBy('name')
            ->get();
    }
}
