<?php

namespace App\Features\Contracts\Application;

use App\Models\Contract;

final class DeleteContract
{
    public function __invoke(Contract $contract): void
    {
        $contract->delete();
    }
}
