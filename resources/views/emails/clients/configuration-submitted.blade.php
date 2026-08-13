<x-mail::message>
# Cliente pendiente de aprobación

Se ha enviado a aprobación la configuración del cliente **{{ $client->fullName() }}**.

**Contrato propuesto:** {{ $clientContract?->contract?->name ?? 'Sin contrato' }}  
**Duración:** {{ $clientContract?->contract?->duration_months ?? '—' }} meses  
**Zona:** {{ $zone?->name ?? 'Sin zona' }}

Se requieren las aprobaciones de Director de Ventas y Director General.

<x-mail::button :url="$approvalsUrl">
Revisar aprobaciones
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
