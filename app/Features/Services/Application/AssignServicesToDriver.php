<?php

namespace App\Features\Services\Application;

use App\Features\Services\Domain\ServiceStatusTransitions;
use App\Models\Driver;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignServicesToDriver
{
    /**
     * @param  list<int>  $serviceIds
     */
    public function __invoke(int $driverId, array $serviceIds): int
    {
        $driver = Driver::query()->find($driverId);

        if ($driver === null) {
            throw ValidationException::withMessages([
                'driver_id' => 'El chofer seleccionado no existe.',
            ]);
        }

        return (int) DB::transaction(function () use ($driverId, $serviceIds): int {
            $validIds = Service::query()
                ->whereIn('id', $serviceIds)
                ->whereHas('manifests')
                ->pluck('id')
                ->all();

            if ($validIds === []) {
                throw ValidationException::withMessages([
                    'service_ids' => 'Ninguna recolección seleccionada tiene un manifiesto válido.',
                ]);
            }

            $services = Service::query()
                ->whereIn('id', $validIds)
                ->lockForUpdate()
                ->get();

            $updated = 0;

            foreach ($services as $service) {
                if (! ServiceStatusTransitions::canTransition(
                    (string) $service->status,
                    Service::STATUS_SCHEDULED,
                ) && $service->status !== Service::STATUS_SCHEDULED) {
                    continue;
                }

                $service->driver_id = $driverId;
                $service->status = Service::STATUS_SCHEDULED;
                $service->save();
                $updated++;
            }

            if ($updated === 0) {
                throw ValidationException::withMessages([
                    'service_ids' => 'Ninguna recolección seleccionada admite asignación en su estado actual.',
                ]);
            }

            return $updated;
        });
    }
}
