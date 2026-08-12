<?php

namespace App\Features\Approvals\Application;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPendingApprovals
{
    public function __invoke(int $perPage = 15): LengthAwarePaginator
    {
        $user = auth()->user();
        $canApprove = $user->can(PermissionTypes::CLIENT_CONTRACTS_APPROVE);
        $canReject = $user->can(PermissionTypes::CLIENT_CONTRACTS_REJECT);
        $userId = $user->id;
        $paginator = Client::query()
            ->with([
                'zone',
                'pendingContract.contract',
                'activeContract.contract',
                'configurationApprovals',
            ])
            ->where('configuration_status', Client::STATUS_PENDING_APPROVAL)
            ->orderByDesc('configuration_submitted_at')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function (Client $client) use ($canApprove, $canReject, $userId) {
            $hasApprovedForUser = $client->configurationApprovals()
                ->where('user_id', $userId)
                ->exists();
            $client->can_approve = $canApprove && ! $hasApprovedForUser;
            $client->can_reject = $canReject && ! $hasApprovedForUser;

            return $client;
        });

        return $paginator;
    }
}