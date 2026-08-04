<?php

namespace App\Features\Clients\Application;

use App\Features\Contracts\Application\Jobs\SendContractApprovalEmailJob;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Mail\ClientConfigurationSubmitted;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class FinalizeClientContractConfiguration
{
    public function __invoke(Client $client): Client
    {
        if (! $client->isConfigurable()) {
            throw ValidationException::withMessages([
                'configuration_status' => 'La configuración del cliente no se puede enviar en su estado actual.',
            ]);
        }

        $pending = $client->contracts()
            ->where('status', ClientContract::STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($pending === null) {
            throw ValidationException::withMessages([
                'contract_id' => 'Debe asignar un contrato antes de enviar a aprobación.',
            ]);
        }

        if ($client->zone_id === null) {
            throw ValidationException::withMessages([
                'zone_id' => 'Debe asignar una zona de recolección antes de enviar a aprobación.',
            ]);
        }

        $client->configuration_status = Client::STATUS_PENDING_APPROVAL;
        $client->configuration_submitted_at = now();
        $client->configuration_reviewed_at = null;
        $client->configuration_rejection_reason = null;
        $client->save();

        $client->configurationApprovals()->delete();

        $client->load(['zone', 'pendingContract.contract', 'activeContract.contract']);
        SendContractApprovalEmailJob::dispatch($client->pendingContract->id);
        // $recipients = User::permission(PermissionTypes::CLIENT_CONTRACTS_APPROVE)->get();

        // if ($recipients->isNotEmpty()) {
        //     Mail::to($recipients)->send(new ClientConfigurationSubmitted($client));
        // }

        return $client;
    }
}
