<?php

namespace App\Features\Invoices\Infrastructure;

use App\Features\Invoices\Domain\InvoiceCommand;

class CreateCfdiCommand implements InvoiceCommand
{
    private const METHOD = 'POST';
    private array $payload;

    public function __construct(
        array $payload
    ) {
        $this->payload = $payload;
    }

    public function getPayload(): ?array
    {

        return $this->payload;       
    }
    public function getUrl(): string
    {
        return 'invoices';
    }
    public function getMethod():string
    {
        return self::METHOD;
    }

}
