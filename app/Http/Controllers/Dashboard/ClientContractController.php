<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Contracts\Application\BuildClientContractPdfData;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\ClientContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class ClientContractController extends Controller
{
    public function __construct(
        private readonly BuildClientContractPdfData $buildClientContractPdfData,
    ) {}

    public function show(ClientContract $clientContract): JsonResponse
    {
        $canViewPdf = Gate::allows(PermissionTypes::CONTRACTS_VIEW_PDF);

        if (! $canViewPdf) {
            abort_unless(
                Gate::allows(PermissionTypes::APPROVALS_VIEW)
                    && (int) $clientContract->status === ClientContract::STATUS_PENDING,
                403,
            );
        }

        return response()->json([
            'data' => ($this->buildClientContractPdfData)((int) $clientContract->id),
        ]);
    }
}
