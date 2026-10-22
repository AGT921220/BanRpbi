<?php

namespace App\Features\Contracts\Application;

use App\Models\ClientContract;
use Illuminate\Validation\ValidationException;

final class BuildClientContractPdfData
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(int|ClientContract $clientContract): array
    {
        if (is_int($clientContract)) {
            $clientContract = ClientContract::query()->findOrFail($clientContract);
        }

        $clientContract->loadMissing([
            'client.state',
            'client.city',
            'contract.rpbiProfiles',
        ]);

        if (! $this->isPdfReady($clientContract)) {
            throw ValidationException::withMessages([
                'contract' => 'El contrato aún no tiene los datos necesarios para generar el PDF.',
            ]);
        }

        $client = $clientContract->client;
        $contract = $clientContract->contract;
        $frequencyLabels = \App\Models\Contract::frequencyLabels();

        return [
            'client_contract_id' => $clientContract->id,
            'status' => $clientContract->status,
            'status_label' => $this->statusLabel($clientContract->status),
            'start_date' => $clientContract->start_date?->format('d/m/Y'),
            'end_date' => $clientContract->end_date?->format('d/m/Y'),
            'price' => $clientContract->price !== null
                ? (float) $clientContract->price
                : ($contract->cost !== null ? (float) $contract->cost : null),
            'notes' => $clientContract->notes ?: $contract->notes,
            'client' => [
                'id' => $client->id,
                'name' => trim(($client->company ?: '').' '.trim(($client->name ?? '').' '.($client->parentarl_surname ?? ''))),
                'rfc' => $client->rfc,
                'nra' => $client->nra,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => collect([
                    $client->street,
                    $client->num_ext,
                    $client->num_int,
                    $client->colony,
                    $client->postal_code,
                    $client->city?->name,
                    $client->state?->name,
                ])->filter()->implode(', '),
            ],
            'contract' => [
                'id' => $contract->id,
                'name' => $contract->name,
                'duration_months' => $contract->duration_months,
                'frequency' => $contract->frequency,
                'frequency_label' => $frequencyLabels[$contract->frequency] ?? $contract->frequency,
                'cost' => $contract->cost !== null ? (float) $contract->cost : null,
                'notes' => $contract->notes,
                'profiles' => $contract->rpbiProfiles
                    ->map(fn ($profile) => [
                        'code' => $profile->code,
                        'name' => $profile->name,
                        'description' => $profile->description,
                    ])
                    ->values()
                    ->all(),
            ],
            'issuer' => config('business.transportista', []),
        ];
    }

    public function isPdfReady(ClientContract $clientContract): bool
    {
        $clientContract->loadMissing(['client', 'contract.rpbiProfiles']);

        if (! $clientContract->client || ! $clientContract->contract) {
            return false;
        }

        if (! in_array($clientContract->status, [
            ClientContract::STATUS_ACTIVE,
            ClientContract::STATUS_COMPLETED,
        ], true)) {
            return false;
        }

        if (! $clientContract->start_date) {
            return false;
        }

        if (! filled($clientContract->contract->name)) {
            return false;
        }

        return $clientContract->contract->rpbiProfiles->isNotEmpty();
    }

    private function statusLabel(int $status): string
    {
        return match ($status) {
            ClientContract::STATUS_PENDING => 'Pendiente',
            ClientContract::STATUS_ACTIVE => 'Vigente',
            ClientContract::STATUS_COMPLETED => 'Completado',
            ClientContract::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconocido',
        };
    }
}
