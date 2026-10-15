<?php

namespace App\Features\Invoices\Application;

use App\Models\Invoice;

class FindInvoice
{
    public function __invoke(int $invoiceId)
    {
        $invoice = Invoice::where('id', $invoiceId)->first();

        return $invoice;
    }
}
