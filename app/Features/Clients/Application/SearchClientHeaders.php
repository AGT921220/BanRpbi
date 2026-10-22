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
            // category: QueryModifierCategory::FILTER,
        );

        $filtered = (clone $filteredQuery)->count();

        $dataQuery = ($this->builderFilter)(
            builder: $filteredQuery,
            modifiers: $modifiers,
            // category: QueryModifierCategory::OPTION,
        );

        $canUpdate = Gate::allows(PermissionTypes::CLIENTS_UPDATE);
        $canDelete = Gate::allows(PermissionTypes::CLIENTS_DELETE);
        $canAssignContracts = Gate::allows(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS);
        $canShowContractPdfPermission = Gate::allows(PermissionTypes::CONTRACTS_VIEW_PDF);
        $canViewContractHistory = Gate::allows(PermissionTypes::CLIENT_CONTRACTS_VIEW_HISTORY);

        $data = $dataQuery
            ->withExists([
                'contracts',
                'activeContract as active_contract_exists',
                'vigenteContracts as has_vigente_contract',
            ])
            ->with([
                'activeContract.contract.rpbiProfiles',
            ])
            ->get()
            ->map(
                static function (Client $client) use (
                    $canUpdate,
                    $canDelete,
                    $canAssignContracts,
                    $canShowContractPdfPermission,
                    $canViewContractHistory,
                ): ClientHeader {
                    $active = $client->activeContract;
                    $pdfReady = $canShowContractPdfPermission
                        && $active
                        && filled($active->contract_id)
                        && filled($active->start_date)
                        && (int) $active->status === \App\Models\ClientContract::STATUS_ACTIVE
                        && $active->contract
                        && $active->contract->rpbiProfiles->isNotEmpty();

                    return new ClientHeader(
                        id: (int) $client->id,
                        fullName: trim("{$client->name} {$client->parentarl_surname}"),
                        email: $client->email,
                        phone: $client->phone,
                        company: $client->company,
                        createdAt: $client->created_at?->format('d/m/Y H:i'),
                        hasContract: (bool) $client->contracts_exists,
                        hasActiveContract: (bool) $client->active_contract_exists,
                        hasCollectionZone: $client->zone_id !== null,
                        configurationStatus: (string) $client->configuration_status,
                        canUpdate: $canUpdate,
                        canDelete: $canDelete,
                        canShowContractPdf: $pdfReady,
                        canConfigure: $canAssignContracts && $client->isConfigurable(),
                        canViewContractHistory: $canViewContractHistory,
                        clientContractId: $active?->id,
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
