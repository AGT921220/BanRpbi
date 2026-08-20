<?php

namespace App\Http\Controllers\Admin;

use App\Features\Approvals\Application\ApproveClientConfiguration;
use App\Features\Approvals\Application\ListPendingApprovals;
use App\Features\Approvals\Application\RejectClientConfiguration;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Services\Application\BulkCreateServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectClientConfigurationRequest;
use App\Models\Client;
use App\Models\ClientConfigurationApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ApprovalController extends Controller
{
    public function __construct(
        private readonly ListPendingApprovals $listPendingApprovals,
        private readonly ApproveClientConfiguration $approveClientConfiguration,
        private readonly RejectClientConfiguration $rejectClientConfiguration,
        private readonly BulkCreateServices $bulkCreateServices,
    ) {}

    public function index()
    {
        $this->authorize(PermissionTypes::APPROVALS_VIEW);

        
        // return ($this->listPendingApprovals)();
        return view('approvals.index', [
            'clients' => ($this->listPendingApprovals)(),
            'requiredApprovalCount' => ClientConfigurationApproval::REQUIRED_COUNT,
        ]);
    }

    public function approve(Client $client): RedirectResponse
    {
        $this->authorize(PermissionTypes::CLIENT_CONTRACTS_APPROVE);

        $client = ($this->approveClientConfiguration)(
            client: $client,
            user: request()->user(),
        );

        if ($client->configuration_status === Client::STATUS_APPROVED) {
            ($this->bulkCreateServices)(
                client: $client,
            );
        }

        $message = $client->configuration_status === Client::STATUS_APPROVED
            ? 'Cliente aprobado correctamente. El contrato quedó vigente.'
            : 'Tu aprobación quedó registrada. Falta la de otra persona.';

        return redirect()
            ->route('approvals.index')
            ->with('success', $message);
    }

    public function reject(
        RejectClientConfigurationRequest $request,
        Client $client,
    ): RedirectResponse {
        $this->authorize(PermissionTypes::APPROVALS_REJECT);

        ($this->rejectClientConfiguration)(
            client: $client,
            reason: $request->validated('reason'),
        );

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Configuración rechazada. El contrato vigente no se modificó.');
    }
}
