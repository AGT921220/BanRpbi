<?php

namespace App\Features\Zones\Application;

use App\Models\Zone;

final class ToggleZoneStatus
{
    public function __invoke(Zone $zone): Zone
    {
        $zone->update([
            'is_active' => ! $zone->is_active,
        ]);

        return $zone->refresh();
    }
}
