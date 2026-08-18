<?php

namespace App\Features\Drivers\Application;

use App\Models\Driver;

final class UpdateDriver
{
    /**
     * @param  array{
     *     name: string,
     *     parentarl_surname: string,
     *     maternal_surname: string,
     *     phone: string,
     *     zone_id: int,
     *     user_id: int
     * }  $data
     */
    public function __invoke(Driver $driver, array $data): Driver
    {
        $driver->update($data);

        return $driver->refresh();
    }
}
