<?php

namespace App\Http\Controllers\Api;

use App\Features\Services\Application\FindServiceHeader;
use App\Features\Services\Application\SearchServiceHeaders;
use App\Features\Services\Domain\ServiceStatusTransitions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use App\Models\Service;
use App\Models\ServiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly SearchServiceHeaders $searchServiceHeaders,
        private readonly FindServiceHeader $findServiceHeader
    ) {}

    public function index(FilterRequest $request)
    {
        $allowedFilters = ['service_date', 'driver_id'];
        $filters = $request->input('filters', []) ?: [];
        $currentUser = auth()->user();

        if ($currentUser?->driver) {
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

        return response()->json($services);
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
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:services,id'],
            'service_details' => ['required', 'array', 'min:1'],
            'service_details.*.id' => ['required', 'integer'],
            'service_details.*.weight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $serviceId = (int) $data['id'];
        $rpbiProfiles = $data['service_details'];

        try {
            $header = DB::transaction(function () use ($serviceId, $rpbiProfiles) {
                $service = Service::query()
                    ->whereKey($serviceId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentUser = auth()->user();
                if ($currentUser?->driver && (int) $service->driver_id !== (int) $currentUser->driver->id) {
                    throw ValidationException::withMessages([
                        'id' => 'La recolección no está asignada a este chofer.',
                    ]);
                }

                ServiceStatusTransitions::assertCanTransition(
                    (string) $service->status,
                    Service::STATUS_COLLECTED,
                );

                foreach ($rpbiProfiles as $profile) {
                    ServiceDetail::query()
                        ->where('service_id', $serviceId)
                        ->where('id', $profile['id'])
                        ->update([
                            'weight' => $profile['weight'],
                        ]);
                }

                $service->status = Service::STATUS_COLLECTED;
                $service->save();

                return ($this->findServiceHeader)($serviceId);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'status' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Servicio capturado correctamente.',
            'success' => true,
            'data' => $header,
        ], 200);
    }
}
