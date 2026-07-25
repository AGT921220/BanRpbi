@extends('layouts.app')

@section('title', 'Editar cliente')
@section('page-title', 'Editar cliente')

@section('page-actions')
    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
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
                    action="{{ route('clients.update', $client) }}"
                    class="card"
                >
                    @csrf
                    @method('PUT')

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user-edit me-2"></i>
                            Datos del cliente
                        </h3>
                    </div>

                    <div class="card-body">
                        @include('clients._form', ['client' => $client])
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('clients.index') }}" class="btn btn-link">
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
