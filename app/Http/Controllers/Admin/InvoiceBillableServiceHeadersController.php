<?php

namespace App\Http\Controllers\Admin;

use App\Features\Invoices\Application\ListBillableServicesForInvoice;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InvoiceBillableServiceHeadersController extends Controller
{
    public function __construct(
        private readonly ListBillableServicesForInvoice $listBillableServicesForInvoice,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(PermissionTypes::INVOICES_CREATE);

        $validated = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
        ]);

        return response()->json([
            'data' => ($this->listBillableServicesForInvoice)((int) $validated['client_id']),
        ]);
    }
}
