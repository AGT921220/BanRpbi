<?php

namespace App\Http\Controllers\Admin;

use App\Features\Manifests\Application\SearchManifestHeaders;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use Illuminate\Http\JsonResponse;

class ManifestHeadersController extends Controller
{
    public function __construct(
        private readonly SearchManifestHeaders $searchManifestHeaders,
    ) {}

    public function index(FilterRequest $request): JsonResponse
    {
        $this->authorize(PermissionTypes::MANIFESTS_VIEW);

        $allowedFilters = [
            'service_date',
            'services.service_date',
            'invoice_id',
            'services.invoice_id',
            'invoices.id',
            'clients.id',
            'manifests.status',
            'status',
        ];

        return response()->json(
            ($this->searchManifestHeaders)(
                $request->queryOptions($allowedFilters),
                $request->draw(),
            ),
        );
    }
}
