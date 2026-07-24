@extends('layouts.app')

@section('title', 'Editar rol')
@section('page-title', 'Editar rol')

@section('page-actions')
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <form method="POST" action="{{ route('roles.update', $role) }}" class="card">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h3 class="card-title">Editar rol</h3>
                    </div>
                    <div class="card-body">
                        @include('roles._form')
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('roles.index') }}" class="btn btn-link">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
