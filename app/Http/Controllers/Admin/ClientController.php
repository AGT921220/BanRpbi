<?php

namespace App\Http\Controllers\Admin;

use App\Features\Clients\Application\CreateClient;
use App\Features\Clients\Application\DeleteClient;
use App\Features\Clients\Application\UpdateClient;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ClientController extends Controller
{
    public function __construct(
        private readonly CreateClient $createClient,
        private readonly UpdateClient $updateClient,
        private readonly DeleteClient $deleteClient,
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::CLIENTS_VIEW);

        return view('clients.index');
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::CLIENTS_CREATE);

        return view('clients.create');
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
            'client' => $client,
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
}
