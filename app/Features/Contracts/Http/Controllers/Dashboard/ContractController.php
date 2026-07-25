<?php

namespace App\Features\Contracts\Http\Controllers\Dashboard;

use App\Features\Contracts\Application\CreateContract;
use App\Features\Contracts\Application\DeleteContract;
use App\Features\Contracts\Application\ListContracts;
use App\Features\Contracts\Application\UpdateContract;
use App\Features\Contracts\Http\Requests\StoreContractRequest;
use App\Features\Contracts\Http\Requests\UpdateContractRequest;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContractController extends Controller
{
    public function __construct(
        private readonly ListContracts $listContracts,
        private readonly CreateContract $createContract,
        private readonly UpdateContract $updateContract,
        private readonly DeleteContract $deleteContract,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize(PermissionTypes::CONTRACTS_VIEW);

        $contracts = ($this->listContracts)(
            search: $request->filled('search')
                ? $request->string('search')->toString()
                : null,
        );

        return view('contracts.index', [
            'contracts' => $contracts,
        ]);
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::CONTRACTS_CREATE);

        return view('contracts.create');
    }

    public function store(StoreContractRequest $request): RedirectResponse
    {
        $this->authorize(PermissionTypes::CONTRACTS_CREATE);

        ($this->createContract)($request->validated());

        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contrato creado correctamente.');
    }

    public function edit(Contract $contract): View
    {
        $this->authorize(PermissionTypes::CONTRACTS_UPDATE);

        return view('contracts.edit', [
            'contract' => $contract,
        ]);
    }

    public function update(UpdateContractRequest $request, Contract $contract): RedirectResponse
    {
        $this->authorize(PermissionTypes::CONTRACTS_UPDATE);

        ($this->updateContract)($contract, $request->validated());

        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contrato actualizado correctamente.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $this->authorize(PermissionTypes::CONTRACTS_DELETE);

        ($this->deleteContract)($contract);

        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contrato eliminado correctamente.');
    }
}
