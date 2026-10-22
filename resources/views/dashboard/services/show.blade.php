@extends('layouts.app')

@section('title', 'Recolección #'.$service->id)
@section('page-title', 'Recolección #'.$service->id)

@section('page-actions')
    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver al listado
    </a>
@endsection

@section('content')
    @php
        $manifest = $service->manifests->first();
        $statusLabel = $header['status']['label'] ?? $service->status;
    @endphp

    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-truck me-2"></i>
                            Datos de la recolección
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Cliente</div>
                                <div class="datagrid-content">
                                    {{ $service->client?->company ?: trim(($service->client?->name ?? '').' '.($service->client?->parentarl_surname ?? '')) ?: '—' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Fecha</div>
                                <div class="datagrid-content">
                                    {{ optional($service->service_date)->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Estado</div>
                                <div class="datagrid-content">{{ $statusLabel }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Zona</div>
                                <div class="datagrid-content">{{ $service->zone?->name ?? '—' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Chofer</div>
                                <div class="datagrid-content">
                                    {{ $service->driver?->fullName() ?? 'Sin asignar' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Manifiesto</div>
                                <div class="datagrid-content">
                                    {{ $manifest?->id ? '#'.$manifest->id : 'Sin manifiesto' }}
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Factura</div>
                                <div class="datagrid-content">
                                    {{ $service->invoice_id ? '#'.$service->invoice_id : 'Sin factura' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-packages me-2"></i>
                            Detalles RPBI
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Perfil</th>
                                    <th>Código</th>
                                    <th>Peso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($service->serviceDetails as $detail)
                                    <tr>
                                        <td>{{ $detail->rpbiProfile?->name ?? '—' }}</td>
                                        <td>{{ $detail->rpbiProfile?->code ?? '—' }}</td>
                                        <td>{{ $detail->weight ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-secondary text-center py-4">
                                            Sin detalles capturados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Documentos</h3>
                    </div>
                    <div class="card-body d-grid gap-2">
                        @can(\App\Features\Permissions\Constants\PermissionTypes::MANIFESTS_DOWNLOAD)
                            @if ($manifest)
                                <a
                                    href="#"
                                    data-id="{{ $manifest->id }}"
                                    class="btn btn-outline-secondary btn-manifest-pdf"
                                >
                                    <i class="ti ti-file-type-pdf me-1"></i>
                                    Manifiesto PDF
                                </a>
                            @endif
                        @endcan

                        @can(\App\Features\Permissions\Constants\PermissionTypes::INVOICES_DOWNLOAD_PDF)
                            @if ($service->invoice_id)
                                <a
                                    href="#"
                                    data-id="{{ $service->invoice_id }}"
                                    class="btn btn-outline-primary btn-invoice-pdf"
                                >
                                    <i class="ti ti-file-invoice me-1"></i>
                                    Factura PDF
                                </a>
                            @endif
                        @endcan

                        @if (! $manifest && ! $service->invoice_id)
                            <div class="text-secondary">
                                No hay documentos disponibles todavía.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/manifests/manifestPdf.js')
    @vite('resources/js/modules/manifests/manifestPdfBuilder.js')
    @vite('resources/js/modules/invoices/invoicePdf.js')
    @vite('resources/js/modules/invoices/invoicePdfBuilder.js')
@endpush
