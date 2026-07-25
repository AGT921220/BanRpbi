<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListContracts
{
    public function __invoke(
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return Contract::query()
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%")
                            ->orWhere('frequency', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
