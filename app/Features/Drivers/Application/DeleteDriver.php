<?php

namespace App\Features\Drivers\Application;

use App\Models\Driver;

final class DeleteDriver
{
    public function __invoke(Driver $driver): void
    {
        $driver->delete();
    }
}
