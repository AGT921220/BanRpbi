<?php

namespace App\Http\Controllers\Admin;

use App\Features\Clients\Application\CreateClient;
use App\Features\Clients\Application\DeleteClient;
use App\Features\Clients\Application\ListClients;
use App\Features\Clients\Application\UpdateClient;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ClientController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const ORDERABLE_COLUMNS = [
        0 => 'name',
        1 => 'email',
        2 => 'phone',
        3 => 'company',
        4 => 'created_at',
    ];

    public function __construct(
        private readonly ListClients $listClients,
        private readonly CreateClient $createClient,
        private readonly UpdateClient $updateClient,
        private readonly DeleteClient $deleteClient,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize(PermissionTypes::CLIENTS_VIEW);

        if (! $this->wantsDataTable($request)) {
            return view('clients.index');
        }

        $modifiers = [];

        $search = $this->resolveSearch($request);

        if ($search !== null) {
            $modifiers[] = QueryFilter::whereAnyLike(
                fields: [
                    'name',
                    'parentarl_surname',
                    'email',
                    'phone',
                    'company',
                ],
                value: $search,
            );
        }

        $orderColumn = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');

        if (
            $orderColumn !== null
            && array_key_exists((int) $orderColumn, self::ORDERABLE_COLUMNS)
        ) {
            $modifiers[] = QueryOptions::orderBy(
                field: self::ORDERABLE_COLUMNS[(int) $orderColumn],
                direction: is_string($orderDirection) ? $orderDirection : 'asc',
            );
        }

        if ($request->has('start')) {
            $modifiers[] = QueryOptions::offset(
                offset: $request->integer('start'),
            );
        }

        if ($request->has('length') && $request->integer('length') > 0) {
            $modifiers[] = QueryOptions::limit(
                limit: $request->integer('length'),
            );
        }

        $result = ($this->listClients)(
            modifiers: $modifiers,
            draw: $request->integer('draw'),
        );

        return response()->json($result);
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

    private function wantsDataTable(Request $request): bool
    {
        return $request->ajax()
            || $request->wantsJson()
            || $request->has('draw');
    }

    private function resolveSearch(Request $request): ?string
    {
        $search = $request->input('search.value', $request->input('search'));

        if (! is_string($search) || trim($search) === '') {
            return null;
        }

        return trim($search);
    }
}
