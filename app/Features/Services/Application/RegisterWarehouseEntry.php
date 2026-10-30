<?php

namespace App\Features\Services\Application;

use App\Models\Manifest;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterWarehouseEntry
{
    public function __construct(
        private readonly FindServiceHeader $findServiceHeader,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function __invoke(int $manifestId, int $userId): array
    {
        $serviceId = DB::transaction(function () use ($manifestId, $userId): int {
            $manifest = Manifest::query()
                ->lockForUpdate()
                ->find($manifestId);

            if ($manifest === null) {
                throw ValidationException::withMessages([
                    'manifest_id' => 'No se encontró el manifiesto.',
                ]);
            }

            $service = Service::query()
                ->whereKey($manifest->service_id)
                ->lockForUpdate()
                ->first();

            if ($service === null) {
                throw ValidationException::withMessages([
                    'manifest_id' => 'El manifiesto no tiene un servicio.',
                ]);
            }

            if ($service->warehouse_entry_at !== null) {
                throw ValidationException::withMessages([
                    'manifest_id' => 'El servicio ya tiene entrada a almacén.',
                ]);
            }

            if ($service->status !== Service::STATUS_COLLECTED) {
                throw ValidationException::withMessages([
                    'manifest_id' => 'El manifiesto '.$manifest->id
                        .' está en estatus '.$this->statusLabel((string) $service->status)
                        .'. Solo se puede registrar la entrada si está Recolectado.',
                ]);
            }

            $service->warehouse_entry_at = now();
            $service->warehouse_entry_user_id = $userId;
            $service->save();

            return (int) $service->id;
        });

        return ($this->findServiceHeader)($serviceId);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Service::STATUS_PENDING => 'Pendiente',
            Service::STATUS_SCHEDULED => 'Programado',
            Service::STATUS_COLLECTED => 'Recolectado',
            // Service::STATUS_IN_WAREHOUSE => 'En almacén',
            Service::STATUS_NEXT => 'Siguiente',
            default => 'Desconocido',
        };
    }
}
