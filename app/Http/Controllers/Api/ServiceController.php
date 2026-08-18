<?php

namespace App\Http\Controllers\Api;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Services\Application\SearchServiceHeaders;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly SearchServiceHeaders $searchServiceHeaders
    ) {}



    public function index(FilterRequest $request)
    {
        $zoneId = Driver::where('user_id', auth()->user()->id)->first()->zone_id;
        // $this->authorize(PermissionTypes::SERVICES_VIEW);
        $allowedFilters = ['service_date', 'zones.id'];
        $now = Carbon::now();
        $filters = [
            [
                'field' => 'zones.id',
                'operator' => 'where',
                'value' => $zoneId,
            ],
            [
                'field' => 'service_date',
                'operator' => 'where',
                'value' => $now->toDateString(),
            ]
        ];
        $request->merge(['filters' => $filters]);
        //     foreach ($this->input('filters', []) as $filter) {
        // $field = $filter['field'] ?? null;
        // $operator = $filter['operator'] ?? null;
        // $value = $filter['value'] ?? null;


        $services = ($this->searchServiceHeaders)($request->queryOptions($allowedFilters), $request->draw());
        return response()->json(
            $services,
        );
    }
}
