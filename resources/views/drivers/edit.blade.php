@extends('layouts.app')

@section('title', 'Editar chofer')
@section('page-title', 'Editar chofer')

@section('page-actions')
    <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form
                    method="POST"
                    action="{{ route('drivers.update', $driver) }}"
                    class="card"
                >
                    @csrf
                    @method('PUT')

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-steering-wheel me-2"></i>
                            Datos del chofer
                        </h3>
                    </div>

                    <div class="card-body">
                        @include('drivers._form', [
                            'driver' => $driver,
                            'zones' => $zones,
                        ])
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('drivers.index') }}" class="btn btn-link">
                            <i class="ti ti-x me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
