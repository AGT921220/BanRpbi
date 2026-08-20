<?php

namespace App\Http\Controllers\Admin;

use App\Features\Clients\Application\CreateClient;
use App\Features\Clients\Application\DeleteClient;
use App\Features\Clients\Application\FinalizeClientContractConfiguration;
use App\Features\Clients\Application\SaveClientConfiguration;
use App\Features\Clients\Application\UpdateClient;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveClientConfigurationRequest;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\SubmitClientConfigurationRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Models\Contract;
use App\Models\State;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ClientController extends Controller
{
    public function __construct(
        private readonly CreateClient $createClient,
        private readonly UpdateClient $updateClient,
        private readonly DeleteClient $deleteClient,
        private readonly SaveClientConfiguration $saveClientConfiguration,
        private readonly FinalizeClientContractConfiguration $finalizeClientContractConfiguration,
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::CLIENTS_VIEW);

        return view('clients.index', [
            'contracts' => Contract::query()
                ->with('rpbiProfiles')
                ->orderBy('name')
                ->get(['id', 'name', 'duration_months', 'frequency', 'notes', 'cost']),
            'zones' => Zone::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'description']),
        ]);
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::CLIENTS_CREATE);

        return view('clients.create', [
            'statesCities' => $this->statesCitiesCatalog(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->authorize(PermissionTypes::CLIENTS_CREATE);

        ($this->createClient)($request->validated());

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client): View
    {
        $this->authorize(PermissionTypes::CLIENTS_UPDATE);

        return view('clients.edit', [
            'client' => $client->load(['state', 'city']),
            'statesCities' => $this->statesCitiesCatalog(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize(PermissionTypes::CLIENTS_UPDATE);

        ($this->updateClient)($client, $request->validated());

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize(PermissionTypes::CLIENTS_DELETE);

        ($this->deleteClient)($client);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    public function showConfiguration(Client $client): JsonResponse
    {
        $this->authorize(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS);

        $client->load([
            'zone',
            'pendingContract.contract.rpbiProfiles',
            'activeContract.contract.rpbiProfiles',
        ]);
        $draft = $client->pendingContract ?? (
            $client->configuration_status === Client::STATUS_APPROVED
                ? null
                : $client->activeContract
        );
        $active = $client->activeContract;
        $selectedProfiles = $draft?->contract?->rpbiProfiles
            ?? $active?->contract?->rpbiProfiles
            ?? collect();
        $selectedProfileIds = $selectedProfiles
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        return response()->json([
            'id' => $client->id,
            'full_name' => $client->fullName(),
            'configuration_status' => $client->configuration_status,
            'can_edit' => $client->isConfigurable(),
            'has_active_contract' => $active !== null,
            'active_contract' => $active?->contract ? [
                'id' => $active->contract->id,
                'name' => $active->contract->name,
                'duration_months' => $active->contract->duration_months,
                'start_date' => $active->start_date?->format('Y-m-d'),
                'end_date' => $active->end_date?->format('Y-m-d'),
            ] : null,
            'contract_id' => $draft?->contract_id,
            'zone_id' => $client->zone_id,
            'start_date' => $draft?->start_date?->format('Y-m-d'),
            'end_date' => $draft?->end_date?->format('Y-m-d'),
            'notes' => $draft?->notes,
            'profile_ids' => $selectedProfileIds,
            'contract' => $draft?->contract ? [
                'id' => $draft->contract->id,
                'name' => $draft->contract->name,
                'duration_months' => $draft->contract->duration_months,
                'frequency' => $draft->contract->frequency,
                'notes' => $draft->contract->notes,
                'cost' => $draft->contract->cost,
            ] : null,
            'zone' => $client->zone ? [
                'id' => $client->zone->id,
                'name' => $client->zone->name,
                'description' => $client->zone->description,
            ] : null,
            'rejection_reason' => $client->configuration_rejection_reason,
        ]);
    }

    public function saveConfiguration(
        SaveClientConfigurationRequest $request,
        Client $client,
    ): JsonResponse {
        $this->authorize(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS);

        $client = ($this->saveClientConfiguration)(
            client: $client,
            data: $request->validated(),
            userId: $request->user()?->id,
        );

        return response()->json([
            'message' => 'Configuración guardada correctamente.',
            'configuration_status' => $client->configuration_status,
            'has_contract' => $client->contracts()->exists(),
            'has_collection_zone' => $client->zone_id !== null,
        ]);
    }

    public function submitConfiguration(
        SubmitClientConfigurationRequest $request,
        Client $client,
    ): JsonResponse {
        $this->authorize(PermissionTypes::CLIENTS_ASSIGN_CONTRACTS);

        $client = ($this->finalizeClientContractConfiguration)($client);

        return response()->json([
            'message' => 'Configuración enviada a aprobación.',
            'configuration_status' => $client->configuration_status,
        ]);
    }

    /**
     * @return list<array{id: int, name: string, cities: list<array{id: int, name: string}>}>
     */
    private function statesCitiesCatalog(): array
    {
        return State::query()
            ->with(['cities' => static fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(static fn (State $state): array => [
                'id' => (int) $state->id,
                'name' => (string) $state->name,
                'cities' => $state->cities
                    ->map(static fn ($city): array => [
                        'id' => (int) $city->id,
                        'name' => (string) $city->name,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }
}
