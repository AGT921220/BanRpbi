<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Invoices\Application\FindInvoice;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly FindInvoice $findInvoice,
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::INVOICES_VIEW);

        return view('dashboard.invoices.index');
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::INVOICES_CREATE);

        return view('dashboard.invoices.create', [
            'clients' => Client::whereHas('services', function ($query) {
                $query->whereNull('invoice_id');
            })
                ->orderBy('id')
                ->get(['id', 'company', 'name', 'parentarl_surname']),
        ]);
    }

    public function show(int $invoiceId): JsonResponse
    {
        $this->authorize(PermissionTypes::INVOICES_VIEW);

        return response()->json([
            'data' => ($this->findInvoice)($invoiceId),
        ]);
    }
}
