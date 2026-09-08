<?php

namespace App\Features\Services\Application;

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
            return Service::query()
                ->whereIn('id', $serviceIds)
                ->update([
                    'driver_id' => $driverId,
                    'status' => Service::STATUS_SCHEDULED,
                ]);
        });
    }
}
