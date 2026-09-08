<?php

namespace App\Http\Controllers\Admin;

use App\Features\Invoices\Jobs\CreateInvoiceFromSelectedServices;
use App\Features\Invoices\Jobs\CreateInvoiceJob;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvoiceRequest;
use Illuminate\Http\RedirectResponse;

final class InvoiceController extends Controller
{
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->authorize(PermissionTypes::INVOICES_CREATE);

        $validated = $request->validated();

        CreateInvoiceFromSelectedServices::dispatch(
            $validated['client_id'],
            $validated['service_ids']
        )->onQueue('invoices');

        // TODO: crear la factura con $validated['client_id'] y $validated['service_ids'].

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Solicitud de factura recibida. Pendiente de implementar.');
    }
}
