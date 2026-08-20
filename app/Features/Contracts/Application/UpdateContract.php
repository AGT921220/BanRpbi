<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;

final class UpdateContract
{
    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     duration_months: int,
     *     frequency: string,
     *     cost: float|string,
     *     profile_ids: list<int>
     * }  $data
     */
    public function __invoke(Contract $contract, array $data): Contract
    {
        return DB::transaction(function () use ($contract, $data): Contract {
            $profileIds = $data['profile_ids'];
            unset($data['profile_ids']);

            $contract->update($data);
            $contract->rpbiProfiles()->sync($profileIds);

            return $contract->refresh()->load('rpbiProfiles');
        });
    }
}
