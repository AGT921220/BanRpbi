<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListContracts
{
    public function __invoke(
        int $perPage = 15,
        ?string $search = null,
        ?string $frequency = null,
    ): LengthAwarePaginator {
        return Contract::query()
            ->with('rpbiProfiles')
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                filled($frequency) && in_array($frequency, Contract::FREQUENCIES, true),
                fn ($query) => $query->where('frequency', $frequency),
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
