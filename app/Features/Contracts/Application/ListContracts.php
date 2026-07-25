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
            ->with('client')
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('folio', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereHas('client', function ($query) use ($search): void {
                                $query
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('company', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->orderByDesc('starts_at')
            ->orderBy('folio')
            ->paginate($perPage)
            ->withQueryString();
    }
}
