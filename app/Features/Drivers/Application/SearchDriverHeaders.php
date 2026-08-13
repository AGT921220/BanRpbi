<?php

namespace App\Features\Drivers\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\Driver;

class SearchDriverHeaders
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
    ) {}

    /**
     * @param  array<int, mixed>  $filters
     * @return array<string, mixed>
     */
    public function __invoke(array $filters = [], int $draw = 1): array
    {
        $data = $this->builderFilter->paginate(
            builder: Driver::select(
                'drivers.id',
                'drivers.name',
                'drivers.parentarl_surname',
                'drivers.maternal_surname',
                'drivers.phone',
                'drivers.zone_id',
                'zones.name as zone_name',
            )
                ->join('zones', 'drivers.zone_id', '=', 'zones.id'),
            modifiers: $filters,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (Driver $driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->fullName(),
                'phone' => $driver->phone,
                'zone' => $driver->zone_name,
            ];
        });

        return $data;
    }
}
