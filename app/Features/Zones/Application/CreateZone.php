<?php

namespace App\Features\Zones\Application;

use App\Models\Zone;

final class CreateZone
{
    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     color?: string|null,
     *     geometry: array{type: string, coordinates: array<int, mixed>},
     *     is_active?: bool
     * }  $data
     */
    public function __invoke(array $data): Zone
    {
        return Zone::query()->create($data);
    }
}
