<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Contracts\Application\BuildClientContractPdfData;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\ClientContract;
use Illuminate\Http\JsonResponse;

final class ClientContractController extends Controller
{
    public function __construct(
        private readonly BuildClientContractPdfData $buildClientContractPdfData,
    ) {}

    public function show(ClientContract $clientContract): JsonResponse
    {
        $this->authorize(PermissionTypes::CONTRACTS_VIEW_PDF);

        return response()->json([
            'data' => ($this->buildClientContractPdfData)((int) $clientContract->id),
        ]);
    }
}
