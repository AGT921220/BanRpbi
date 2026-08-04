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
                'zone_id',
                'configuration_status',
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
        $canAssignContracts = Gate::allows(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS);

        $data = $dataQuery
            ->withExists('contracts')
            ->get()
            ->map(
                static function (Client $client) use ($canUpdate, $canDelete, $canAssignContracts): ClientHeader {
                    $status = (string) $client->configuration_status;

                    return new ClientHeader(
                        id: (int) $client->id,
                        fullName: trim("{$client->name} {$client->parentarl_surname}"),
                        email: $client->email,
                        phone: $client->phone,
                        company: $client->company,
                        createdAt: $client->created_at?->format('d/m/Y H:i'),
                        hasContract: (bool) $client->contracts_exists,
                        hasCollectionZone: $client->zone_id !== null,
                        configurationStatus: $status,
                        canUpdate: $canUpdate,
                        canDelete: $canDelete,
                        canConfigure: $canAssignContracts
                            && $status !== Client::STATUS_PENDING_APPROVAL
                            && in_array($status, [
                                Client::STATUS_CONFIGURATION_PENDING,
                                Client::STATUS_REJECTED,
                                Client::STATUS_APPROVED,
                            ], true),
                    );
                },
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
