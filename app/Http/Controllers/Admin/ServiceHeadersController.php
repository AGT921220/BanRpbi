<?php

namespace App\Http\Controllers\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Services\Application\SearchServiceHeaders;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use Illuminate\Http\JsonResponse;

final class ServiceHeadersController extends Controller
{
    public function __construct(private readonly SearchServiceHeaders $searchServiceHeaders) {}


    public function index(FilterRequest $request): JsonResponse
    {
        $this->authorize(PermissionTypes::COLLECTIONS_VIEW);
        $allowedFilters = ['service_date'];
        $services = ($this->searchServiceHeaders)($request->queryOptions($allowedFilters), $request->draw());
        return response()->json(
            $services,
        );
    }
}
