<?php

namespace App\Features\Invoices\Domain;

interface InvoiceCommand
{
    public function getPayload(): ?array;
    public function getUrl(): string;
    public function getMethod(): string;
}
