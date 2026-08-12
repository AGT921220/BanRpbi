<?php

namespace App\Features\Clients\Application;

use App\Models\Client;
use App\Models\ClientContract;
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
     *     notes?: string|null,
     *     profile_ids?: list<int>|null
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
                ];

                if ($pending) {
                    $pending->update($payload);
                } else {
                    // Si hay ACTIVE, esto crea el contrato de reemplazo sin tocarlo.
                    $pending = $client->contracts()->create($payload);
                }

                $client->configurationApprovals()->delete();
            }

            if (array_key_exists('profile_ids', $data)) {
                if ($pending !== null) {
                    $pending->rpbiProfiles()->sync($data['profile_ids'] ?? []);
                } elseif (! empty($data['profile_ids'])) {
                    throw ValidationException::withMessages([
                        'profile_ids' => 'Debe asignar un contrato antes de seleccionar perfiles.',
                    ]);
                }
            }

            return $client->fresh([
                'zone',
                'pendingContract.contract',
                'pendingContract.rpbiProfiles',
                'activeContract.contract',
            ]) ?? $client;
        });
    }
}
