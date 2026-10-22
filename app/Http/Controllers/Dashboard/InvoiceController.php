<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Invoices\Application\FindInvoice;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Infrastructure\Facturapi\FacturapiClient;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly FindInvoice $findInvoice,
        private readonly FacturapiClient $facturapiClient,
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
            'clients' => Client::query()
                ->orderBy('company')
                ->orderBy('name')
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

    public function downloadPdf(int $invoiceId): Response|JsonResponse
    {
        $this->authorize(PermissionTypes::INVOICES_DOWNLOAD_PDF);

        $invoice = Invoice::query()->findOrFail($invoiceId);

        if (! filled($invoice->external_id) || ! config('app.facturapi_enabled')) {
            return response()->json([
                'message' => 'No hay PDF oficial disponible para esta factura.',
                'data' => ($this->findInvoice)($invoiceId),
            ], 404);
        }

        $pdf = $this->facturapiClient->downloadInvoicePdf((string) $invoice->external_id);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="factura-'.$invoice->id.'.pdf"',
        ]);
    }
}
