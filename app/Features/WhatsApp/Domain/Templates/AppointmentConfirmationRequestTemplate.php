<?php

namespace App\Features\WhatsApp\Domain\Templates;

use App\Features\WhatsApp\Domain\Interfaces\WhatsappTemplate;
use App\Models\Appointment;

class AppointmentConfirmationRequestTemplate implements WhatsappTemplate
{
    // private const TEMPLATE_SID = 'HXf58fd3a38e244b656ed79173ce74c06a';
    private const TEMPLATE_SID = 'HX490ff86206cb3680d79781142a2cc360';
    public function __construct(
        private int $appointmentId,
        private string $doctor,
        private string $patientName,
        private string $appointmentDate,
        private string $appointmentTime,
        private string $location,
        private string $contact,
    ) {}

    public static function getName(): string
    {
        return 'appointment_confirmation_request';
    }

    public static function getTwilioName(): string
    {
        return 'appointment_confirmation_request';
    }

    public static function getDisplayName(): string
    {
        return 'Whatsapp de Recordatorio     de cita';
    }

    public function getVariables(): array
    {
        return [
            '1'=> $this->patientName,
            '2' => $this->appointmentDate,
            '3' => $this->appointmentTime,
            '4' => $this->doctor,
            '5' => $this->location,
            '6' => $this->contact,
            '7' => json_encode([
                'type' => 'confirm_appointment',
                'appointment_id' => $this->appointmentId,
            ]), // payload botón 1
            '8' => json_encode([
                'type' => 'cancel_appointment',
                'appointment_id' => $this->appointmentId,
            ]), 
        ];
    }
    // $contentVariables = [
    //     '1' => (string) $doctor,
    //     '2' => (string) $fecha,
    //     '3' => (string) $hora,
    //     '4' => (string) $ubicacion,
    //     '5' => (string) $contacto,
    //     '6' => (string) 'CONFIRM_APPOINTMENT:APT-12345', // payload botón 1
    //     '7' => (string) 'RESCHEDULE_APPOINTMENT:APT-12345', // payload botón 2
    // ];
    public function getTemplateSid(): string
    {
        return self::TEMPLATE_SID;
    }
}
