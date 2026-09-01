<?php

namespace App\Features\Clients\Application;

use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SaveClientConfiguration
{
    /**
     * @param  array{
     *     contract_id?: int|null,
     *     zone_id?: int|null,
     *     start_date?: string|null,
     *     end_date?: string|null,
     *     notes?: string|null
     * }  $data
     */
    public function __invoke(Client $client, array $data, ?int $userId = null): Client
    {
        if (! $client->isConfigurable()) {
            throw ValidationException::withMessages([
                'configuration_status' => 'La configuración del cliente no se puede editar en su estado actual.',
            ]);
        }

        return DB::transaction(function () use ($client, $data, $userId): Client {
            if (array_key_exists('zone_id', $data)) {
                $client->zone_id = $data['zone_id'];
            }

            // Borrador / reemplazo en curso. Las recolecciones siguen el contrato ACTIVE vigente.
            $client->configuration_status = Client::STATUS_CONFIGURATION_PENDING;
            $client->configuration_rejection_reason = null;
            $client->save();

            $pending = $client->contracts()
                ->where('status', ClientContract::STATUS_PENDING)
                ->latest('id')
                ->first();

            if (! empty($data['contract_id'])) {
                $payload = [
                    'contract_id' => $data['contract_id'],
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'status' => ClientContract::STATUS_PENDING,
                    'user_id' => $userId,
                    'price' => Contract::query()->whereKey($data['contract_id'])->value('cost'),
                ];

                // info($data['generate_invoice']);
                if(!!isset($data['generate_invoice'])) {
                    $payload['generate_initial_invoice'] = $data['generate_invoice'];
                    $payload['initial_invoice_manifest_count'] = $data['invoice_manifest_count'];
                }
                
                // info($payload);
                if ($pending) {
                    $pending->update($payload);
                } else {
                    // Si hay ACTIVE, esto crea el contrato de reemplazo sin tocarlo.
                    $pending = $client->contracts()->create($payload);
                }

                $client->configurationApprovals()->delete();
            }

            return $client->fresh([
                'zone',
                'pendingContract.contract.rpbiProfiles',
                'activeContract.contract.rpbiProfiles',
            ]) ?? $client;
        });
    }
}
