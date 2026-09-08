<?php

namespace App\Features\WhatsApp\Domain\Templates;

use App\Features\WhatsApp\Domain\Interfaces\WhatsappTemplate;

final class ClientServiceReminderTemplate implements WhatsappTemplate
{
    private const TEMPLATE_SID = 'HX24caab4ed8f59d65f4ead29b17e290de';

    public function __construct(
        private string $clientName,
        private string $date,
        private string $driverName,
        private string $manifestNumber,
        private string $residues,
        private string $manifestUuid,
    ) {}

    public static function getName(): string
    {
        return 'client_service_reminder';
    }

    public static function getTwilioName(): string
    {
        return 'rpbi_testing_client_service_reminder';
    }

    public static function getDisplayName(): string
    {
        return 'WhatsApp de recordatorio de recolección';
    }

    public function getVariables(): array
    {
        return [
            '1' => $this->clientName,
            '2' => $this->date,
            '3' => $this->driverName,
            '4' => $this->manifestNumber,
            '5' => $this->residues,
            '6' => $this->manifestUuid,
        ];
    }

    public function getTemplateSid(): string
    {
        return self::TEMPLATE_SID;
    }
}