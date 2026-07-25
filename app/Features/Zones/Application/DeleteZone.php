<?php

namespace App\Features\Zones\Application;

use App\Models\Zone;
use RuntimeException;

final class DeleteZone
{
    public function __invoke(Zone $zone): void
    {
        // Cuando existan relaciones (por ejemplo zone_id en clientes o recolecciones),
        // validar aquí antes de eliminar. Hoy no hay dependencias registradas.

        if (! $zone->delete()) {
            throw new RuntimeException('No fue posible eliminar la zona.');
        }
    }
}
