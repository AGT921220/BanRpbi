<?php

namespace App\Http\Controllers\Api;

use App\Features\Services\Application\SearchServiceHeaders;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly SearchServiceHeaders $searchServiceHeaders
    ) {}

    public function index(FilterRequest $request)
    {
        $allowedFilters = ['service_date', 'driver_id'];
        $now = Carbon::now();
        $filters = $request->input('filters', []) ?:  [];
        $currentUser = auth()->user();

        if (!!$currentUser->driver) {
            $filters = array_merge($filters, [
                [
                    'field' => 'driver_id',
                    'operator' => 'where',
                    'value' => $currentUser->driver->id,
                ],
            ]);
            $request->merge(['filters' => $filters]);
        }

        
        $services = ($this->searchServiceHeaders)($request->queryOptions($allowedFilters), $request->draw());

        return response()->json(
            $services,
        );
    }
}
