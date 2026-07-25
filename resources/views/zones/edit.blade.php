@extends('layouts.app')

@section('title', 'Editar zona')
@section('page-title', 'Editar zona')

@section('page-actions')
    <a href="{{ route('zones.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <form
            method="POST"
            action="{{ route('zones.update', $zone) }}"
            class="card"
            id="zone-form"
        >
            @csrf
            @method('PUT')

            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-map-pin me-2"></i>
                    Datos de la zona
                </h3>
            </div>

            <div class="card-body">
                @include('zones._form', ['zone' => $zone, 'existingZones' => $existingZones])
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('zones.index') }}" class="btn btn-link">
                    <i class="ti ti-x me-1"></i>
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="zone-submit-btn" disabled>
                    <i class="ti ti-device-floppy me-1"></i>
                    Actualizar
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/modules/zones/form.js'])
@endpush
