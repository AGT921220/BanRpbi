<?php

namespace App\Features\Manifests\Application;

use App\Models\Client;
use App\Models\Manifest;
use App\Models\ServiceDetail;

class FindManifestHeader
{
    /**
     * @return array<string, mixed>|null
     */
    public function __invoke(int $manifestId): ?array
    {
        $manifest = Manifest::query()
            ->with([
                'service.client.city',
                'service.client.state',
                'service.driver',
                'service.serviceDetails.rpbiProfile',
            ])
            ->find($manifestId);

        if ($manifest === null) {
            return null;
        }

        $status = (string) $manifest->status;

        return [
            'id' => $manifest->id,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'folio' => $manifest->id,
            'client' => $this->client($manifest->service?->client),
            'driver' => $manifest->service?->driver?->fullName() ?? '',
            'transportista' => config('business.transportista', []),
            'vehicle' => [
                'sct' => 'DATO PENDIENTE',
                'plates' => 'DATO PENDIENTE',
                'type' => 'DATO PENDIENTE',
            ],
            'route' => 'DATO PENDIENTE',
            'details' => $this->details($manifest),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function client(?Client $client): array
    {
        if ($client === null) {
            return [
                'name' => '',
                'nra' => '',
                'postal_code' => '',
                'street' => '',
                'num_ext' => '',
                'num_int' => '',
                'colony' => '',
                'phone' => '',
                'email' => '',
                'city' => ['name' => ''],
                'state' => ['name' => ''],
            ];
        }

        $name = $client->company ?: trim($client->name.' '.$client->parentarl_surname);

        return [
            'name' => (string) $name,
            'nra' => (string) ($client->nra ?? ''),
            'postal_code' => (string) ($client->postal_code ?? ''),
            'street' => (string) ($client->street ?? ''),
            'num_ext' => (string) ($client->num_ext ?? ''),
            'num_int' => (string) ($client->num_int ?? ''),
            'colony' => (string) ($client->colony ?? ''),
            'phone' => (string) ($client->phone ?? ''),
            'email' => (string) ($client->email ?? ''),
            'city' => ['name' => (string) ($client->city?->name ?? '')],
            'state' => ['name' => (string) ($client->state?->name ?? '')],
        ];
    }

    /**
     * @return list<array{id: int|null, name: string, code: string, description: string}>
     */
    private function details(Manifest $manifest): array
    {
        $details = $manifest->service?->serviceDetails;

        if ($details === null) {
            return [];
        }

        return $details
            ->map(function (ServiceDetail $detail): array {
                $profile = $detail->rpbiProfile;

                return [
                    'id' => $profile?->id,
                    'name' => (string) ($profile?->name ?? ''),
                    'code' => (string) ($profile?->code ?? ''),
                    'description' => (string) ($profile?->description ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Manifest::STATUS_PENDING => 'Pendiente',
            Manifest::STATUS_COLLECTED => 'Recolectado',
            Manifest::STATUS_IN_PROCESS => 'En proceso',
            Manifest::STATUS_PROCESSED => 'Procesado',
            Manifest::STATUS_INVOICED => 'Facturado',
            default => 'Desconocido',
        };
    }
}
