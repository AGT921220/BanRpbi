@extends('layouts.app')

@section('title', 'Nuevo contrato')
@section('page-title', 'Nuevo contrato')

@section('page-actions')
    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('contracts.store') }}" class="card">
                    @csrf

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-file-plus me-2"></i>
                            Datos del contrato
                        </h3>
                    </div>

                    <div class="card-body">
                        @include('contracts._form', ['rpbiProfiles' => $rpbiProfiles])
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('contracts.index') }}" class="btn btn-link">
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
