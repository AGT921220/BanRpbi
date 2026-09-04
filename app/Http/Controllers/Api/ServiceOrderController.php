<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        info($request->all());
        return response()->json([
            'message' => 'Servicios ordenados correctamente.',
        ], 201);
    }
    public function store(Request $request)
    {
        try {

            $currentUser = auth()->user();

            $request->validate([
                'service_ids' => ['required', 'array', 'min:1'],
                'service_ids.*' => ['required', 'integer', 'exists:services,id'],
            ]);

            $serviceIds = $request->input('service_ids');

            $case = collect($serviceIds)
                ->map(function ($serviceId, $index) {
                    return "WHEN {$serviceId} THEN " . ($index + 1);
                })
                ->implode(' ');

            $services = Service::whereIn('id', $serviceIds);
            if (!!$currentUser->driver) {
                $services->where('driver_id', $currentUser->driver->id);
            }
            $services->update([
                'route_order' => DB::raw("
                    CASE id
                        {$case}
                    END
                "),
            ]);

            return response()->json([
                'message' => 'Servicios ordenados correctamente.',
            ], 200);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Ocurrió un error al ordenar los servicios.',
            ], 500);
        }
    }
}
