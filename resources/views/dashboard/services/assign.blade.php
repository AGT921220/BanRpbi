@extends('layouts.app')

@section('title', 'Asignar recolecciones')
@section('page-title', 'Asignar recolecciones')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::COLLECTIONS_VIEW)
        <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-eye me-1"></i>
            Ver recolecciones
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        @include('dashboard.services.filters')

        <form
            method="POST"
            action="{{ route('services.assign.store') }}"
            id="assign-services-form"
        >
            @csrf
            <input type="hidden" name="service_date" value="{{ $serviceDate }}">

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-checklist me-2"></i>
                                Recolecciones del día
                            </h3>
                            <div class="card-actions">
                                <label class="form-check m-0">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="select-all-services"
                                    >
                                    <span class="form-check-label">Seleccionar todas</span>
                                </label>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($services->isEmpty())
                                <div class="text-secondary text-center py-4">
                                    No hay recolecciones para esta fecha.
                                </div>
                            @else
                                <div class="divide-y" id="services-checklist">
                                    @foreach ($services as $service)
                                        @php
                                            $isAssigned = $service->driver_id !== null;
                                            $clientLabel = $service->client?->company
                                                ?: trim(($service->client?->name ?? '').' '.($service->client?->parentarl_surname ?? ''));
                                        @endphp
                                        <label
                                            class="row align-items-center py-3 cursor-pointer {{ $isAssigned ? 'opacity-75' : '' }}"
                                            for="service-{{ $service->id }}"
                                        >
                                            <span class="col-auto">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input service-checkbox @error('service_ids') is-invalid @enderror"
                                                    name="service_ids[]"
                                                    id="service-{{ $service->id }}"
                                                    value="{{ $service->id }}"
                                                    @checked(collect(old('service_ids', []))->contains((string) $service->id))
                                                >
                                            </span>
                                            <span class="col">
                                                <span class="fw-bold">
                                                    #{{ $service->id }} — {{ $clientLabel ?: 'Sin cliente' }}
                                                </span>
                                                <span class="d-block text-secondary">
                                                    Zona: {{ $service->zone?->name ?? 'Sin zona' }}
                                                    · Estado:
                                                    {{ $service->status === \App\Models\Service::STATUS_SCHEDULED ? 'Programada' : 'Pendiente' }}
                                                </span>
                                                @if ($isAssigned)
                                                    <span class="d-block text-secondary small">
                                                        Asignada a: {{ $service->driver?->fullName() ?? 'Chofer #'.$service->driver_id }}
                                                    </span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            @error('service_ids')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-steering-wheel me-2"></i>
                                Chofer
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required" for="driver_id">Seleccionar chofer</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-user"></i>
                                    </span>
                                    <select
                                        name="driver_id"
                                        id="driver_id"
                                        class="form-select @error('driver_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">Selecciona un chofer</option>
                                        @foreach ($drivers as $driver)
                                            <option
                                                value="{{ $driver->id }}"
                                                @selected((string) old('driver_id') === (string) $driver->id)
                                            >
                                                {{ $driver->fullName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('driver_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($drivers->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    No hay choferes registrados. Crea uno antes de asignar.
                                </div>
                            @endif
                        </div>

                        <div class="card-footer d-flex justify-content-end gap-2">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                @disabled($services->isEmpty() || $drivers->isEmpty())
                            >
                                <i class="ti ti-user-check me-1"></i>
                                Asignar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/services/assign.js')
@endpush
