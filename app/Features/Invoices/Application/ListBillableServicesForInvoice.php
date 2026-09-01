<?php

namespace App\Features\Invoices\Application;

use App\Models\Service;
use Illuminate\Support\Collection;

final class ListBillableServicesForInvoice
{
    /**
     * @return Collection<int, array{
     *     id: int,
     *     service_date: string,
     *     status: string,
     *     zone: string|null,
     *     manifest_id: int|null
     * }>
     */
    public function __invoke(int $clientId): Collection
    {
        return Service::query()
            ->with([
                'zone:id,name',
                'manifests:id,service_id',
            ])
            ->where('client_id', $clientId)
            ->whereNull('invoice_id')
            ->orderBy('service_date')
            ->orderBy('id')
            ->get()
            ->map(function (Service $service): array {
                return [
                    'id' => $service->id,
                    'service_date' => $service->service_date?->format('Y-m-d') ?? '',
                    'status' => $service->status,
                    'zone' => $service->zone?->name,
                    'manifest_id' => $service->manifests->first()?->id,
                ];
            });
    }
}
