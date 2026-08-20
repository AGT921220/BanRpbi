<?php

namespace App\Http\Controllers\Api;

use App\Features\Services\Application\SearchServiceHeaders;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use Illuminate\Support\Carbon;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly SearchServiceHeaders $searchServiceHeaders
    ) {}

    public function index(FilterRequest $request)
    {
        $allowedFilters = ['service_date'];
        $now = Carbon::now();
        $filters = [
            [
                'field' => 'service_date',
                'operator' => 'where',
                'value' => $now->toDateString(),
            ],
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
