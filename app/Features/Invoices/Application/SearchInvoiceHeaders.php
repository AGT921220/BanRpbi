<?php

namespace App\Features\Invoices\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\Invoice;

class SearchInvoiceHeaders
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
    ) {}

    /**
     * @param  array<int, mixed>  $filters
     * @return array<string, mixed>
     */
    public function __invoke(array $filters = [], int $draw = 1): array
    {
        $data = $this->builderFilter->paginate(
            builder: Invoice::select(
                'invoices.id',
                'invoices.status',
                'invoices.external_id',
                'invoices.created_at',
                'clients.company',
            )
                ->leftJoin('clients', 'invoices.client_id', '=', 'clients.id'),
            modifiers: $filters,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (Invoice $invoice) {
            return [
                'id' => $invoice->id,
                'status' => $this->resolveStatus($invoice->status),
                'client' => $invoice->company,
                'external_id' => $invoice->external_id,
                'created_at' => $invoice->created_at?->format('d/m/Y H:i'),
            ];
        });

        return $data;
    }

    private function resolveStatus(?string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            default => $status ?: 'Desconocido',
        };
    }
}
