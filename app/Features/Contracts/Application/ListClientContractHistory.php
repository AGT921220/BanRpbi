<?php

namespace App\Features\Contracts\Application;

use App\Models\Client;
use App\Models\ClientContract;
use Illuminate\Support\Collection;

final class ListClientContractHistory
{
    public function __construct(
        private readonly BuildClientContractPdfData $buildClientContractPdfData,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function __invoke(Client $client): Collection
    {
        $frequencyLabels = \App\Models\Contract::frequencyLabels();

        return $client->contracts()
            ->with(['contract.rpbiProfiles'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (ClientContract $clientContract) use ($frequencyLabels): array {
                $contract = $clientContract->contract;

                return [
                    'id' => $clientContract->id,
                    'contract_id' => $clientContract->contract_id,
                    'contract_name' => $contract?->name,
                    'frequency' => $contract?->frequency,
                    'frequency_label' => $frequencyLabels[$contract?->frequency] ?? $contract?->frequency,
                    'duration_months' => $contract?->duration_months,
                    'notes' => $clientContract->notes ?: $contract?->notes,
                    'price' => $clientContract->price !== null
                        ? (float) $clientContract->price
                        : ($contract?->cost !== null ? (float) $contract->cost : null),
                    'start_date' => $clientContract->start_date?->format('d/m/Y'),
                    'end_date' => $clientContract->end_date?->format('d/m/Y'),
                    'status' => $clientContract->status,
                    'status_label' => match ($clientContract->status) {
                        ClientContract::STATUS_PENDING => 'Pendiente',
                        ClientContract::STATUS_ACTIVE => 'Vigente',
                        ClientContract::STATUS_COMPLETED => 'Completado',
                        ClientContract::STATUS_CANCELLED => 'Cancelado',
                        default => 'Desconocido',
                    },
                    'profiles' => $contract
                        ? $contract->rpbiProfiles
                            ->map(fn ($profile) => $profile->code ?: $profile->name)
                            ->filter()
                            ->values()
                            ->all()
                        : [],
                    'can_show_pdf' => ($this->buildClientContractPdfData)->isPdfReady($clientContract),
                ];
            });
    }
}
