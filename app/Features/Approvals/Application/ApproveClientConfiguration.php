<?php

namespace App\Features\Approvals\Application;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\ClientConfigurationApproval;
use App\Models\ClientContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ApproveClientConfiguration
{
    public function __invoke(Client $client, User $user): Client
    {
        if ($client->configuration_status !== Client::STATUS_PENDING_APPROVAL) {
            throw ValidationException::withMessages([
                'configuration_status' => 'Solo se pueden aprobar clientes pendientes de aprobación.',
            ]);
        }

        if (! $user->can(PermissionTypes::CLIENT_CONTRACTS_APPROVE)) {
            throw ValidationException::withMessages([
                'permission' => 'No tienes permiso para aprobar.',
            ]);
        }

        return DB::transaction(function () use ($client, $user): Client {
            $pending = $client->contracts()
                ->where('status', ClientContract::STATUS_PENDING)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($pending === null) {
                throw ValidationException::withMessages([
                    'contract_id' => 'No hay un contrato pendiente de aprobación.',
                ]);
            }

            $existing = ClientConfigurationApproval::query()
                ->where('client_contract_id', $pending->id)
                ->lockForUpdate()
                ->get();

            if ($existing->contains('user_id', $user->id)) {
                throw ValidationException::withMessages([
                    'user_id' => 'Ya registraste tu aprobación para este contrato.',
                ]);
            }

            if ($existing->count() >= ClientConfigurationApproval::REQUIRED_COUNT) {
                throw ValidationException::withMessages([
                    'approvals' => 'Este contrato ya tiene las aprobaciones requeridas.',
                ]);
            }

            ClientConfigurationApproval::query()->create([
                'client_contract_id' => $pending->id,
                'client_id' => $client->id,
                'user_id' => $user->id,
                'role_name' => $user->getRoleNames()->first() ?: 'Aprobador',
                'approved_at' => now(),
            ]);

            $approvedCount = $existing->count() + 1;

            if ($approvedCount < ClientConfigurationApproval::REQUIRED_COUNT) {
                return $client->fresh([
                    'pendingContract.contract',
                    'activeContract.contract',
                    'configurationApprovals.user',
                ]) ?? $client;
            }

            $client->contracts()
                ->where('status', ClientContract::STATUS_ACTIVE)
                ->update(['status' => ClientContract::STATUS_CANCELLED]);

            $pending->update(['status' => ClientContract::STATUS_ACTIVE]);

            $client->configuration_status = Client::STATUS_APPROVED;
            $client->configuration_reviewed_at = now();
            $client->configuration_rejection_reason = null;
            $client->save();

            return $client->fresh([
                'pendingContract.contract',
                'activeContract.contract',
                'configurationApprovals.user',
            ]) ?? $client;
        });
    }
}
