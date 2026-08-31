<?php

namespace App\Features\Invoices\Application;

use App\Models\Invoice;

class CreateInvoice
{
    public function __invoke(int $clientId): int
    {
        $invoice  = new Invoice();
        // $invoice->service_id = $serviceId;
        $invoice->client_id = $clientId;
        $invoice->save();
        return $invoice->id;
    }
}
