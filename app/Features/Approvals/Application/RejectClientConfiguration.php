<?php

namespace App\Features\Approvals\Application;

use App\Models\Client;
use App\Models\ClientContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RejectClientConfiguration
{
    public function __invoke(Client $client, ?string $reason = null): Client
    {
        if ($client->configuration_status !== Client::STATUS_PENDING_APPROVAL) {
            throw ValidationException::withMessages([
                'configuration_status' => 'Solo se pueden rechazar clientes pendientes de aprobación.',
            ]);
        }

        return DB::transaction(function () use ($client, $reason): Client {
            $pendingIds = $client->contracts()
                ->where('status', ClientContract::STATUS_PENDING)
                ->pluck('id');

            if ($pendingIds->isNotEmpty()) {
                $client->configurationApprovals()
                    ->whereIn('client_contract_id', $pendingIds)
                    ->delete();

                $client->contracts()
                    ->whereIn('id', $pendingIds)
                    ->update(['status' => ClientContract::STATUS_CANCELLED]);
            }

            $client->configuration_status = Client::STATUS_REJECTED;
            $client->configuration_reviewed_at = now();
            $client->configuration_rejection_reason = $reason;
            $client->save();

            return $client->fresh(['activeContract.contract', 'pendingContract.contract']) ?? $client;
        });
    }
}
