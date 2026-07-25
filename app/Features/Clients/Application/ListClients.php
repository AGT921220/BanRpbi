<?php

namespace App\Features\Clients\Application;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListClients
{
    public function __invoke(
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return Client::query()
            ->select([
                'id',
                'name',
                'parentarl_surname',
                'email',
                'phone',
                'company',
            ])
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('parentarl_surname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('name')
            ->orderBy('parentarl_surname')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Client $client): Client {
                $client->has_contract = $client->contracts()->exists();

                return $client;
            });
    }
}
