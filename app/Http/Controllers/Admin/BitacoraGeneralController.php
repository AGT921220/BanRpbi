<?php

namespace App\Http\Controllers\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\QueryOptions;
use App\Features\WasteProcess\Application\SearchBitacoraGeneral;
use App\Http\Controllers\Controller;
use App\Models\Service;
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
            'services.collected_at',
            'services.warehouse_entry_at',
            'clients.company',
            'manifests.id',
            'drivers.name',
            'rpbi_profiles.code',
            'rpbi_profiles.name',
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
                dateField: $this->dateField($request),
                clientId: $this->positiveId($request, 'client_id'),
                status: $this->status($request),
                manifestId: $this->positiveId($request, 'manifest_id'),
                driverId: $this->positiveId($request, 'driver_id'),
                profileId: $this->positiveId($request, 'rpbi_profile_id'),
                options: [
                    QueryOptions::offset($offset),
                    QueryOptions::limit($limit),
                    QueryOptions::orderBy($orderBy, $direction),
                ],
                draw: $draw,
            ),
        );
    }

    private function dateField(Request $request): string
    {
        $dateField = $request->string('date_field')->toString();

        if (in_array($dateField, ['service_date', 'collected_at', 'warehouse_entry_at'], true)) {
            return $dateField;
        }

        return 'service_date';
    }

    private function status(Request $request): string
    {
        $status = $request->string('status')->toString();
        $allowed = [
            Service::STATUS_PENDING,
            Service::STATUS_SCHEDULED,
            Service::STATUS_COLLECTED,
            Service::STATUS_IN_PROCESS,
            Service::STATUS_PROCESSED,
            Service::STATUS_NEXT,
        ];

        if (in_array($status, $allowed, true)) {
            return $status;
        }

        return '';
    }

    private function positiveId(Request $request, string $key): ?int
    {
        $id = $request->integer($key);

        if ($id <= 0) {
            return null;
        }

        return $id;
    }
}
