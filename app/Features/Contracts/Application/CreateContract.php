<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;

final class CreateContract
{
    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     duration_months: int,
     *     frequency: string
     * }  $data
     */
    public function __invoke(array $data): Contract
    {
        return Contract::query()->create($data);
    }
}
