<?php

namespace App\Features\Clients\Application;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\BuilderFilter;
use App\Features\Shared\Query\QueryModifierCategory;
use App\Models\Client;
use Illuminate\Support\Facades\Gate;

final readonly class SearchClientHeaders
{
    public function __construct(
        private BuilderFilter $builderFilter,
    ) {}

    /**
     * @param  array<int, mixed>  $modifiers
     */
    public function __invoke(
        array $modifiers = [],
        int $offset = 0,
        int $limit = 20,
    ): ClientHeaderSearchResult {
        $total = Client::query()->count();

        $filteredQuery = ($this->builderFilter)(
            builder: Client::query()->select([
                'id',
                'name',
                'parentarl_surname',
                'email',
                'phone',
                'company',
                'created_at',
            ]),
            modifiers: $modifiers,
            category: QueryModifierCategory::FILTER,
        );

        $filtered = (clone $filteredQuery)->count();

        $dataQuery = ($this->builderFilter)(
            builder: $filteredQuery,
            modifiers: $modifiers,
            category: QueryModifierCategory::OPTION,
        );

        $canUpdate = Gate::allows(PermissionTypes::CLIENTS_UPDATE);
        $canDelete = Gate::allows(PermissionTypes::CLIENTS_DELETE);

        $data = $dataQuery
            ->withExists('contracts')
            ->get()
            ->map(
                static fn (Client $client): ClientHeader => new ClientHeader(
                    id: (int) $client->id,
                    fullName: trim("{$client->name} {$client->parentarl_surname}"),
                    email: $client->email,
                    phone: $client->phone,
                    company: $client->company,
                    createdAt: $client->created_at?->format('d/m/Y H:i'),
                    hasContract: (bool) $client->contracts_exists,
                    canUpdate: $canUpdate,
                    canDelete: $canDelete,
                ),
            );

        $perPage = max($limit, 1);
        $currentPage = intdiv(max($offset, 0), $perPage) + 1;
        $lastPage = max((int) ceil($filtered / $perPage), 1);

        return new ClientHeaderSearchResult(
            data: $data,
            total: $total,
            filtered: $filtered,
            currentPage: $currentPage,
            perPage: $perPage,
            lastPage: $lastPage,
        );
    }
}
