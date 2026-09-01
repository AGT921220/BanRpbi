@extends('layouts.app')

@section('title', 'Crear factura')
@section('page-title', 'Crear factura')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::INVOICES_VIEW)
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-eye me-1"></i>
            Ver facturas
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <form
            method="POST"
            action="{{ route('admin.invoices.create') }}"
            id="create-invoice-form"
            data-old-service-ids="{{ implode(',', old('service_ids', [])) }}"
        >
            @csrf

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-building-store me-2"></i>
                                Cliente
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="mb-0">
                                <label class="form-label required" for="client_id">Seleccionar cliente</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-user"></i>
                                    </span>
                                    <select
                                        name="client_id"
                                        id="client_id"
                                        class="form-select @error('client_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">Selecciona un cliente</option>
                                        @foreach ($clients as $client)
                                            @php
                                                $clientLabel = $client->company
                                                    ?: trim("{$client->name} {$client->parentarl_surname}");
                                            @endphp
                                            <option
                                                value="{{ $client->id }}"
                                                @selected((string) old('client_id') === (string) $client->id)
                                            >
                                                {{ $clientLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('client_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-checklist me-2"></i>
                                Servicios por facturar
                            </h3>
                            <div class="card-actions">
                                <label class="form-check m-0">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="select-all-services"
                                        disabled
                                    >
                                    <span class="form-check-label">Seleccionar todos</span>
                                </label>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="text-secondary text-center py-4" id="services-empty-message">
                                Selecciona un cliente para ver sus servicios pendientes de factura.
                            </div>

                            <div class="divide-y d-none" id="services-checklist"></div>

                            @error('service_ids')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card-footer d-flex justify-content-end gap-2">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="create-invoice-submit"
                                disabled
                            >
                                <i class="ti ti-file-invoice me-1"></i>
                                Crear factura
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/invoices/create.js')
@endpush
