<?php

namespace App\Features\Shared\Infrastructure\Facturapi;

use App\Bussines\Shared\Infrastructure\Facturapi\Interfaces\FacturapiCommand;
use App\Features\Invoices\Domain\InvoiceCommand;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;

class FacturapiClient
{
    private $httpClient;
    private $token;
    private $baseUri;
    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->token = config('app.facturapi_key');
        $this->baseUri = config('app.facturapi_uri');
    }

    public function execCommand(
        InvoiceCommand $command
    ): Response {

        $body = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getToken()
            ],
            'json' => $command->getPayload()
        ];
        $facturapiEnabled = config('app.facturapi_enabled');
        if ($facturapiEnabled) {
            return $this->httpClient->request(
                $command->getMethod(),
                $this->getBaseUri() . $command->getUrl(),
                $body
            );
        }
        $body = json_encode($this->makeFacturApiRequest());

        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }
    private function getBaseUri(): string
    {
        return $this->baseUri;
    }
    private function getToken(): string
    {
        return $this->token;
    }

    private function makeFacturApiRequest()
    {
        $invioceId = Str::random(12);
        return [
            'id' => $invioceId,
            'verification_url' => $this->getVerificationUrl($invioceId),
            'stamp' => [
                'sat_signature' => base64_encode(hex2bin('5450464a376434764377414f416d5035682b52463077764549366f3d')),
                'complement_string' =>  "||1.1|802821f0-43ce-4c27-af43-42e5eb48d354|2023-07-03T19:54:44|" . base64_encode(random_bytes(100))
                    . "|20001000000300022323|| aleatorio",
                'sat_cert_number' =>  Str::random(20),
                'signature' => Str::random(204),
            ],
        ];
    }
    private function getVerificationUrl(string $invioceId)
    {
        return "" . $invioceId;
        // $re = 'XIA190128J61'; // RFC del emisor
        // $rr = 'GARU861010CP0'; // RFC del receptor
        // $tt = '0.000000'; // Total de la factura
        // $fe = 'SeKylw=='; // Sello del CFDI

        // return "https://fake.facturaelectronica.sat.gob.mx/default.aspx?id={$invioceId}&re={$re}&rr={$rr}&tt={$tt}&fe={$fe}";
    }
}
