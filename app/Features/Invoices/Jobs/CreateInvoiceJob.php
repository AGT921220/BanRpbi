<?php

namespace App\Features\Invoices\Jobs;

use App\Features\Invoices\Application\CreateInvoice;
use App\Features\Invoices\Infrastructure\CreateCfdiCommand;
use App\Features\Shared\Infrastructure\Facturapi\FacturapiClient;
use App\Models\Invoice;
use App\Models\Service;
use Generator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateInvoiceJob implements ShouldQueue
{
    use Queueable;

    private int $serviceId;
    private int $clientId;
    /**
     * Create a new job instance.
     */
    public function __construct(int $serviceId, int $clientId)
    {
        $this->serviceId = $serviceId;
        $this->clientId = $clientId;
    }

    /**
     * Execute the job.
     */
    public function handle(CreateInvoice $createInvoice, FacturapiClient $facturapiClient): void
    {
        $invoiceId = $createInvoice->__invoke($this->serviceId, $this->clientId);

        $externalInvoice = $this->createExternalInvoice($invoiceId, $facturapiClient);


        $invoice = Invoice::where('id', $invoiceId)->first();
        $facturapiId = $externalInvoice['id'];
        $verificationUrl = $externalInvoice['verification_url'];
        $invoice->facturapi_id = $facturapiId;
        $invoice->verification_url = $verificationUrl;

        $invoice->sello_sat = $externalInvoice['stamp']['sat_signature'];
        $invoice->cadena_complemento = $externalInvoice['stamp']['complement_string'];
        $invoice->serie_sat = $externalInvoice['stamp']['sat_cert_number'];
        $invoice->sello_cfdi = $externalInvoice['stamp']['signature'];
        $qrcode = new Generator();
        $destination = public_path() . '/invoices/' . $invoice->id . '.svg';
        $qrcode->size(500)->generate($invoice->verification_url, $destination);
        $invoice->save();

    }
    private function createExternalInvoice(int $invoiceId, FacturapiClient $facturapiClient):array
    {
        $service = Service::where('id', $this->serviceId)
            ->with(['client', 'serviceDetails.rpbiProfile'])
            ->first();


        $rpbiProfiles = $service->serviceDetails->map(function ($serviceDetail) {
            return $serviceDetail->rpbiProfile;
        });

        $items =  array_map(function (array $rpbiProfile) {
            return [
                'quantity' => 1,
                'product' => [
                    "description" => $rpbiProfile['description'],
                    "product_key" => "76121900", //PENDIENTE DARME PRODUCT KEY
                    "price" => 1000
                ]
            ];
        }, $rpbiProfiles->toArray());

        $client = $service->client;
        $datosFactura = [
            'type' => "T",
            "customer" => [
                "legal_name" => $client->name . ' ' . $client->parentarl_surname,
                "email" => $client->email,
                //"tax_id" => "GARU861010CP0",
                "tax_id" => $client->rfc, //CAMBIAR
                "tax_system" => "601", //LO TOMAMOS DEL CLIENTE
                "address" => [
                    "zip" => $client->postal_code
                    // "zip" => "22637"
                ]
            ],
            "items" =>
            $items,
            // "payment_form" => 3,
            "folio_number" => $this->serviceId, //ID DE BASE DE DATOS
            // "series" => "F"
        ];
        $command = new CreateCfdiCommand($datosFactura);
        $response = $facturapiClient->execCommand($command);
        return json_decode($response->getBody()->getContents(), true);
    }
}
