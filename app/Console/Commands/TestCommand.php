<?php

namespace App\Console\Commands;

use App\Features\Invoices\Jobs\CreateInvoiceFromNextServices;
use App\Features\Manifests\Jobs\CreateDailyManifestsJob;
use App\Features\Manifests\Jobs\CreateDailyManifestsJobShouldQueue;
use App\Features\Services\Application\BulkCreateServices;
use App\Features\WhatsApp\Application\UseCases\SendWhatsappTemplate;
use App\Features\WhatsApp\Domain\Templates\AppointmentConfirmationRequestTemplate;
use App\Features\WhatsApp\Domain\Templates\ClientServiceReminderTemplate;
use App\Jobs\TestHorizonJob;
use App\Mail\ClientConfigurationSubmitted;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


#[Signature('app:test-command')]
#[Description('Command description')]
class TestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BulkCreateServices $bulkCreateServices, SendWhatsappTemplate $sendWhatsappTemplate): void
    {

        $to = '6144950659';
        $randomUuid = Str::uuid()->toString();
        $template = new ClientServiceReminderTemplate(
            'Alfredo',
            '2022-10-01',
            'Alfredo Chofer',
            'BAN-12345',
            'Cultivos y cepas, Objetos punzocortantes',
            $randomUuid
        );
        // $template = new AppointmentConfirmationRequestTemplate(
        //     1,
        //     'Alvaro Guilebaldo',
        //     'Alfredo Paciente',
        //     'BAN-12345',
        //     'Cultivos y cepas, Objetos punzocortantes',
        //     '2022-10-01',
        //     '6144950659'
        // );
        //         private string $clientName,
        // private string $date,
        // private string $driverName,
        // private string $manifestNumber,
        // private string $residues,
        // private string $manifestUuid,
        $sendWhatsappTemplate->__invoke($to, $template);

        return;
        info('Se envía a crear manifiestos');
        CreateDailyManifestsJob::dispatch();
        info('Se envía a crear manifiestos');
        return;
        $serviceId = 54;
        $service = Service::where('id', $serviceId)
            ->with(['client', 'serviceDetails.rpbiProfile'])
            ->first();
        CreateInvoiceFromNextServices::dispatch($serviceId, $service->client_id)->onQueue('invoices');
        return;

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
            "folio_number" => $invoiceId, //ID DE BASE DE DATOS
            // "series" => "F"
        ];

        // $command = new CreateCfdiCommand($datosFactura);
        // $response = $this->facturapiClient->execCommand($command);


        return;

        $client = Client::first();

        $bulkCreateServices(
            client: $client
        );
        //        Mail::to(config('app.default_mail'))->send(new ClientConfigurationSubmitted($client));

        //        TestHorizonJob::dispatch();
    }
}
