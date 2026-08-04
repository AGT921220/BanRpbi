<?php

namespace App\Features\Zones\Application;

use App\Models\Zone;
use RuntimeException;

final class DeleteZone
{
    public function __invoke(Zone $zone): void
    {
        if ($zone->clients()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar la zona porque tiene clientes asociados.',
            );
        }

        if (! $zone->delete()) {
            throw new RuntimeException('No fue posible eliminar la zona.');
        }
    }
}
