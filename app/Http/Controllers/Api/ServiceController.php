<?php

namespace App\Http\Controllers\Api;

use App\Features\Services\Application\FindServiceHeader;
use App\Features\Services\Application\SearchServiceHeaders;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use App\Models\Service;
use App\Models\ServiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly SearchServiceHeaders $searchServiceHeaders,
        private readonly FindServiceHeader $findServiceHeader
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
    public function show(int $serviceId)
    {
        $service = ($this->findServiceHeader)($serviceId);

        return response()->json([
            'data' => $service,
            'success' => true,
        ], 200);
    }
    public function store(Request $request)
    {

        $serviceId = $request->input('id');
        $rpbiProfiles = $request->input('service_details', []);
        info('Service ID: ' . $serviceId);
        info('RPBI Profiles: ' . json_encode($rpbiProfiles));
        foreach ($rpbiProfiles as $profile) {
            info('Profile: ' . json_encode($profile));
            ServiceDetail::where('service_id', $serviceId)
                ->where('id', $profile['id'])
                ->update([
                    'weight' => $profile['weight'],
                ]);
        }

        Service::where('id', $serviceId)
            ->update([
                'status' => Service::STATUS_COLLECTED,
            ]);

        return response()->json([
            'message' => 'Servicio capturado correctamente.',
            'success' => true,
        ], 200);
    }
}
