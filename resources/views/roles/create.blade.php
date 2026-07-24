@extends('layouts.app')

@section('title', 'Nuevo rol')
@section('page-title', 'Nuevo rol')

@section('page-actions')
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <form method="POST" action="{{ route('roles.store') }}" class="card">
                    @csrf
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-shield-plus me-2"></i>
                            Datos del rol
                        </h3>
                    </div>
                    <div class="card-body">
                        @include('roles._form')
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('roles.index') }}" class="btn btn-link">
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
