<?php

namespace App\Features\Invoices\Application;

use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use App\Features\Permissions\Constants\PermissionTypes;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindInvoice
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(int $invoiceId, ?int $restrictToClientId = null): array
    {
        if (! Gate::allows(PermissionTypes::INVOICES_VIEW)
            && ! Gate::allows(PermissionTypes::INVOICES_DOWNLOAD_PDF)) {
            throw new AccessDeniedHttpException('No autorizado para consultar facturas.');
        }

        $invoice = Invoice::with([
            'client' => function ($q) {
                $q->select([
                    'id',
                    'company',
                    'name',
                    'parentarl_surname',
                    'nra',
                    'rfc',
                    'email',
                    'phone',
                    'postal_code',
                    'street',
                    'num_ext',
                    'num_int',
                    'colony',
                    'state_id',
                    'city_id',
                ])->with([
                    'state:id,name',
                    'city:id,name',
                ]);
            },
            'service' => function ($q) {
                $q->select(['id', 'client_id', 'service_date', 'invoice_id'])
                    ->with([
                        'serviceDetails.rpbiProfile',
                        'manifests:id,service_id',
                        'client' => function ($clientQuery) {
                            $clientQuery->select([
                                'id',
                                'company',
                                'name',
                                'parentarl_surname',
                                'nra',
                                'rfc',
                                'email',
                                'phone',
                                'postal_code',
                                'street',
                                'num_ext',
                                'num_int',
                                'colony',
                                'state_id',
                                'city_id',
                            ])->with([
                                'state:id,name',
                                'city:id,name',
                            ]);
                        },
                    ]);
            },
        ])->find($invoiceId);

        if (! $invoice) {
            throw new NotFoundHttpException('Factura no encontrada.');
        }

        if ($restrictToClientId !== null && (int) $invoice->client_id !== $restrictToClientId) {
            throw new AccessDeniedHttpException('La factura no pertenece al cliente autorizado.');
        }

        $clientModel = $invoice->client ?? $invoice->service?->client;
        $clientName = $clientModel
            ? trim(($clientModel->company ?: '').' '.trim(($clientModel->name ?? '').' '.($clientModel->parentarl_surname ?? '')))
            : null;

        $details = $invoice->service?->serviceDetails ?? collect();
        $manifestId = $invoice->service?->manifests?->first()?->id;
        $issuer = config('business.transportista', []);

        $items = $details->map(function ($detail) use ($manifestId) {
            $rpbiProfile = $detail->rpbiProfile;
            $weight = $detail->weight !== null && $detail->weight !== ''
                ? (float) $detail->weight
                : null;

            return [
                'id' => $rpbiProfile?->id,
                'name' => $rpbiProfile?->name,
                'code' => $rpbiProfile?->code,
                'description' => $rpbiProfile?->description,
                'product_key' => null,
                'quantity' => $weight,
                'price' => null,
                'discount' => null,
                'weight' => $weight,
                'manifest_id' => $manifestId,
            ];
        })->values()->all();

        return [
            'id' => $invoice->id,
            'folio' => $invoice->id,
            'uuid' => $invoice->external_id,
            'external_id' => $invoice->external_id,
            'status' => $invoice->status,
            'verification_url' => $invoice->verification_url,
            'sello_cfdi' => $invoice->sello_cfdi,
            'sello_sat' => $invoice->sello_sat,
            'cadena_complemento' => $invoice->cadena_complemento,
            'serie_sat' => $invoice->serie_sat,
            'qr_code' => $invoice->qr_code,
            'iva_percentage' => $invoice->iva_percentaje ?: null,
            'payment_form' => $invoice->payment_form ?? null,
            'payment_method' => $invoice->payment_method ?? null,
            'currency' => $invoice->currency ?? 'MXN',
            'created_at' => $invoice->created_at?->format('d/m/Y H:i:s'),
            'certified_at' => $invoice->updated_at?->format('Y-m-d\TH:i:s'),
            'service_date' => $invoice->service?->service_date?->format('Y-m-d'),
            'client' => $clientModel ? [
                'id' => $clientModel->id,
                'name' => trim($clientName ?: '') ?: null,
                'rfc' => $clientModel->rfc,
                'nra' => $clientModel->nra,
                'email' => $clientModel->email,
                'phone' => $clientModel->phone,
                'postal_code' => $clientModel->postal_code,
                'street' => $clientModel->street,
                'num_ext' => $clientModel->num_ext,
                'num_int' => $clientModel->num_int,
                'colony' => $clientModel->colony,
                'city' => $clientModel->city?->name,
                'state' => $clientModel->state?->name,
            ] : null,
            'issuer' => [
                'razon_social' => $issuer['razon_social'] ?? null,
                'address' => $issuer['address'] ?? null,
                'postal_code' => $issuer['postal_code'] ?? null,
                'street' => $issuer['street'] ?? null,
                'num_ext' => $issuer['num_ext'] ?? null,
                'num_int' => $issuer['num_int'] ?? null,
                'colony' => $issuer['colony'] ?? null,
                'city' => $issuer['city'] ?? null,
                'state' => $issuer['state'] ?? null,
                'phone' => $issuer['phone'] ?? null,
                'rfc' => $issuer['rfc'] ?? env('TRANSPORTISTA_RFC'),
                'autorizacion_spa' => $issuer['autorizacion_spa'] ?? null,
                'aut_semarnat' => $issuer['aut_semarnat'] ?? null,
            ],
            'items' => $items,
            'has_official_pdf' => filled($invoice->external_id) && (bool) config('app.facturapi_enabled'),
        ];
    }
}
