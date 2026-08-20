<?php

namespace App\Features\Drivers\Application;

use App\Models\Driver;

final class CreateDriver
{
    /**
     * @param  array{
     *     name: string,
     *     parentarl_surname: string,
     *     maternal_surname: string,
     *     phone: string,
     *     user_id: int
     * }  $data
     */
    public function __invoke(array $data): Driver
    {
        return Driver::query()->create($data);
    }
}
