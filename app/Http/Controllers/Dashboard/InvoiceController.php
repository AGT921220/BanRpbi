<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $this->authorize(PermissionTypes::INVOICES_VIEW);

        return view('dashboard.invoices.index');
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::INVOICES_CREATE);

        return view('dashboard.invoices.create', [
            'clients' => Client::query()
                ->orderBy('company')
                ->orderBy('name')
                ->get(['id', 'company', 'name', 'parentarl_surname']),
        ]);
    }

    public function show(int $invoiceId)
    {
        return response()->json(
            ['data' => $this->getInvoice($invoiceId)],
            200
        );
    }

    private function getInvoice(int $invoiceId): array
    {
        $invoice = Invoice::with(['client' => function ($q) {
            $q->select([
                'id',
                'company as name',
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
            ])->with(['state' => function ($q) {
                $q->select(['id', 'name']);
            }])
                ->with(['city' => function ($q) {
                    $q->select(['id', 'name']);
                }]);
        }, 'service' => function ($q) {
            $q->with(['serviceDetails' => function ($q) {
                $q->with('rpbiProfile');
            }, 'manifests' => function ($q) {
                $q->select(['id', 'service_id']);
            }]);
            $q->select(['id', 'client_id', 'service_date']);
        }])->findOrFail($invoiceId);

        $client = $invoice->client ?? $invoice->service?->client;
        $unitPrice = 100;
        $details = $invoice->service?->serviceDetails ?? collect();
        $manifestId = $invoice->service?->manifests?->first()?->id;

        return [
            'folio' => $invoice->id,
            'uuid' => $invoice->external_id,
            'status' => $invoice->status,
            'verification_url' => $invoice->verification_url,
            'sello_cfdi' => $invoice->sello_cfdi,
            'sello_sat' => $invoice->sello_sat,
            'cadena_complemento' => $invoice->cadena_complemento,
            'serie_sat' => $invoice->serie_sat,
            'qr_code' => $invoice->qr_code,
            'iva_percentage' => $invoice->iva_percentaje ?: '16',
            'created_at' => $invoice->created_at?->format('d/m/Y H:i:s'),
            'certified_at' => $invoice->updated_at?->format('Y-m-d\TH:i:s'),
            'service_date' => $invoice->service?->service_date,
            'client' => $client,
            'issuer' => config('business.transportista'),
            'items' => $details->map(function ($detail) use ($unitPrice, $manifestId) {
                $rpbiProfile = $detail->rpbiProfile;

                return [
                    'id' => $rpbiProfile?->id,
                    'name' => $rpbiProfile?->name,
                    'code' => $rpbiProfile?->code,
                    'description' => $rpbiProfile?->description,
                    'product_key' => '76121900',
                    'quantity' => 1,
                    'price' => $unitPrice,
                    'discount' => 0,
                    'manifest_id' => $manifestId,
                ];
            })->values(),
        ];
    }
}
