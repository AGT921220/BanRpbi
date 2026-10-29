<?php

namespace App\Features\Manifests\Application;

use App\Models\Manifest;

class FindManifestHeader
{
    /**
     * @return array{id: int, status: string, status_label: string}|null
     */
    public function __invoke(int $manifestId): ?array
    {
        $manifest = Manifest::query()->find($manifestId);

        if ($manifest === null) {
            return null;
        }

        $status = (string) $manifest->status;

        return [
            'id' => $manifest->id,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
        ];
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
