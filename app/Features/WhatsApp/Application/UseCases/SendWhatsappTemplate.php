<?php

namespace App\Features\WhatsApp\Application\UseCases;

use App\Features\WhatsApp\Domain\Interfaces\WhatsappTemplate;
use App\Features\WhatsApp\Infrastructure\Twilio\TwilioWhatsAppClient;

class SendWhatsappTemplate
{
    private TwilioWhatsAppClient $twilioWhatsappClient;
    public function __construct(  
        TwilioWhatsAppClient $twilioWhatsappClient
    ) {
        $this->twilioWhatsappClient = $twilioWhatsappClient;
    }

    public function __invoke(string $to, WhatsappTemplate $template): void
    {
        // dd($template->getVariables());
        $this->twilioWhatsappClient->sendTemplateMessage($to, $template->getTemplateSid(), $template->getVariables());
    }
}
