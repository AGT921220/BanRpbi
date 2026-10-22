<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Contracts\Application\ListClientContractHistory;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContract;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ClientContractHistoryController extends Controller
{
    public function __construct(
        private readonly ListClientContractHistory $listClientContractHistory,
    ) {}

    public function index(Request $request, Client $client): View
    {
        $this->authorize(PermissionTypes::CLIENT_CONTRACTS_VIEW_HISTORY);

        $status = $request->filled('status')
            ? $request->integer('status')
            : null;

        $history = ($this->listClientContractHistory)($client)
            ->when(
                $status !== null,
                fn ($rows) => $rows->where('status', $status)->values(),
            );

        return view('clients.contracts.history', [
            'client' => $client->loadMissing(['zone']),
            'history' => $history,
            'statusFilter' => $status,
            'statusOptions' => [
                ClientContract::STATUS_PENDING => 'Pendiente',
                ClientContract::STATUS_ACTIVE => 'Vigente',
                ClientContract::STATUS_COMPLETED => 'Completado',
                ClientContract::STATUS_CANCELLED => 'Cancelado',
            ],
            'canViewContractPdf' => auth()->user()?->can(PermissionTypes::CONTRACTS_VIEW_PDF) ?? false,
        ]);
    }
}
