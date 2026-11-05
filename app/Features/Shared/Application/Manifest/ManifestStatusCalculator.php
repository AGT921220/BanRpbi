<?php

namespace App\Features\Shared\Application\Manifest;

class ManifestStatusCalculator
{
    public function __invoke(string $status, ?bool $hasWarehouseEntry = null, ?bool $hasProcess = null, ?bool $hasCompletedProcess = null): string
    {
        if ($hasWarehouseEntry && !$hasProcess) {
            return "En Almacén";
        }
        if ($hasProcess && !$hasCompletedProcess) {
            return "En Proceso";
        }
        if ($hasCompletedProcess) {
            return "Cerrado";
        }
        return match ($status) {
            'pending' => 'Pendiente',
            'scheduled' => 'Programado',
            'collected' => 'Recolectado',
            default => $status,
        };
    }
}
