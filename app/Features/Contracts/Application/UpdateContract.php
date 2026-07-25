<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;

final class UpdateContract
{
    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     duration_months: int,
     *     frequency: string
     * }  $data
     */
    public function __invoke(Contract $contract, array $data): Contract
    {
        $contract->update($data);

        return $contract->refresh();
    }
}
