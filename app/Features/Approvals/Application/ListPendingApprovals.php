<?php

namespace App\Features\Approvals\Application;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPendingApprovals
{
    public function __invoke(int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->with([
                'zone',
                'pendingContract.contract',
                'activeContract.contract',
                'configurationApprovals',
            ])
            ->where('configuration_status', Client::STATUS_PENDING_APPROVAL)
            ->orderByDesc('configuration_submitted_at')
            ->paginate($perPage);
    }
}
