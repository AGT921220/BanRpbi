<?php

namespace App\Features\Approvals\Application;

use App\Features\Permissions\Constants\RoleTypes;
use App\Models\Client;
use App\Models\ClientConfigurationApproval;
use App\Models\ClientContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ApproveClientConfiguration
{
    public function __construct() {}
    public function __invoke(Client $client, User $user): Client
    {
        if ($client->configuration_status !== Client::STATUS_PENDING_APPROVAL) {
            throw ValidationException::withMessages([
                'configuration_status' => 'Solo se pueden aprobar clientes pendientes de aprobación.',
            ]);
        }

        $approverRole = $this->resolveApproverRole($user);

        if ($approverRole === null) {
            throw ValidationException::withMessages([
                'role' => 'Solo Director Ventas o Director General pueden aprobar.',
            ]);
        }

        $pending = $client->contracts()
            ->where('status', ClientContract::STATUS_PENDING)
            ->latest('id')
            ->first();

        if ($pending === null) {
            throw ValidationException::withMessages([
                'contract_id' => 'No hay un contrato pendiente de aprobación.',
            ]);
        }

        return DB::transaction(function () use ($client, $user, $approverRole, $pending): Client {
            ClientConfigurationApproval::query()->updateOrCreate(
                [
                    'client_contract_id' => $pending->id,
                    'role_name' => $approverRole,
                ],
                [
                    'client_id' => $client->id,
                    'user_id' => $user->id,
                    'approved_at' => now(),
                ],
            );

            $approvedRoles = ClientConfigurationApproval::query()
                ->where('client_contract_id', $pending->id)
                ->pluck('role_name')
                ->all();

            $allApproved = collect(RoleTypes::APPROVAL_DIRECTOR_ROLES)
                ->every(static fn (string $role): bool => in_array($role, $approvedRoles, true));

            if (! $allApproved) {
                return $client->fresh([
                    'pendingContract.contract',
                    'activeContract.contract',
                    'configurationApprovals',
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
                'configurationApprovals',
            ]) ?? $client;
        });
    }

    private function resolveApproverRole(User $user): ?string
    {
        foreach (RoleTypes::APPROVAL_DIRECTOR_ROLES as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }
}
