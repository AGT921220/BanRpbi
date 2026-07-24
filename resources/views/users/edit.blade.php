@extends('layouts.app')

@section('title', 'Editar usuario')
@section('page-title', 'Editar usuario')

@section('page-actions')
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Volver
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <form method="POST" action="{{ route('users.update', $user) }}" class="card">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-user-edit me-2"></i>
                            Editar usuario
                        </h3>
                    </div>
                    <div class="card-body">
                        @include('users._form')
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('users.index') }}" class="btn btn-link">
                            <i class="ti ti-x me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
