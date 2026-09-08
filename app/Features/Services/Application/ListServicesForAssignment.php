<?php

namespace App\Features\Services\Application;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class ListServicesForAssignment
{
    /**
     * @return Collection<int, Service>
     */
    public function __invoke(string $serviceDate): Collection
    {
        $date = Carbon::parse($serviceDate)->toDateString();

        return Service::query()
            ->with([
                'client:id,company,name,parentarl_surname',
                'zone:id,name',
                'driver:id,name,parentarl_surname,maternal_surname',
            ])
            ->whereDate('service_date', $date)
            ->orderBy('id')
            ->get();
    }
}
