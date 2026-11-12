<?php

namespace App\Http\Controllers\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\QueryOptions;
use App\Features\WasteProcess\Application\SearchBitacoraGeneral;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BitacoraGeneralController extends Controller
{
    public function __construct(
        private readonly SearchBitacoraGeneral $searchBitacoraGeneral,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(PermissionTypes::BITACORA_GENERAL_VIEW);

        $offset = max($request->integer('offset', 0), 0);
        $limit = $request->integer('limit', 20);

        if ($limit <= 0) {
            $limit = 20;
        }

        $draw = max($request->integer('draw', 1), 1);
        $orderBy = $request->string('order_by')->toString();
        $allowed = [
            'service_details.service_id',
            'service_details.id',
            'service_details.weight',
            'services.service_date',
            'services.status',
            'services.warehouse_entry_at',
            'clients.company',
            'manifests.id',
            'drivers.name',
        ];

        if (! in_array($orderBy, $allowed, true)) {
            $orderBy = 'services.service_date';
        }

        $direction = $request->string('order_direction')->toString();

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return response()->json(
            ($this->searchBitacoraGeneral)(
                dateFrom: $request->string('date_from')->toString(),
                dateTo: $request->string('date_to')->toString(),
                options: [
                    QueryOptions::offset($offset),
                    QueryOptions::limit($limit),
                    QueryOptions::orderBy($orderBy, $direction),
                ],
                draw: $draw,
            ),
        );
    }
}
