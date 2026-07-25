<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;

final class CreateContract
{
    /**
     * @param  array{
     *     folio: string,
     *     client_id: int,
     *     name: string,
     *     starts_at: string,
     *     ends_at: string,
     *     status: string,
     *     collection_frequency?: string|null,
     *     notes?: string|null
     * }  $data
     */
    public function __invoke(array $data): Contract
    {
        return Contract::query()->create($data);
    }
}
