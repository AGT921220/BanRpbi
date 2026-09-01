@extends('layouts.app')

@section('title', 'Facturas')
@section('page-title', 'Facturas')

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-file-invoice me-2"></i>
                    Listado de facturas
                </h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table
                        id="invoices-table"
                        class="table table-vcenter card-table w-100"
                        data-url="{{ route('invoice-headers.index') }}"
                    >
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Status</th>
                                <th>Cliente</th>
                                <th>UUID</th>
                                <th>Fecha</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/invoices/index.js')
    @vite('resources/js/modules/invoices/invoicePdf.js')
    @vite('resources/js/modules/invoices/invoicePdfBuilder.js')
@endpush
