@extends('layouts.app')

@section('title', 'Nuevo cliente')
@section('page-title', 'Nuevo cliente')

@section('page-actions')
    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form method="POST" action="{{ route('clients.store') }}" class="card">
                    @csrf

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user-plus me-2"></i>
                            Datos del cliente
                        </h3>
                    </div>

                    <div class="card-body">
                        @include('clients._form')
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('clients.index') }}" class="btn btn-link">
                            <i class="ti ti-x me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/clients/form.js')
@endpush
