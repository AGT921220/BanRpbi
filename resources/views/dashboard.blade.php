@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('page-actions')
    <a href="#" class="btn btn-primary">
        <i class="ti ti-plus me-2"></i>
        Nueva recolección
    </a>
@endsection

@section('content')
    <div class="container-xl">
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Bienvenido
                        </h3>
                    </div>

                    <div class="card-body">
                        Has iniciado sesión como:
                        <strong>
                            {{ auth()->user()->name }}
                        </strong>
                    </div>
                </div>
            </div>
        <div class="row row-deck row-cards">

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">
                                Recolecciones
                            </div>
                        </div>

                        <div class="h1 mb-3">
                            0
                        </div>

                        <div class="text-secondary">
                            Total registrado
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">
                            Generadores
                        </div>

                        <div class="h1 mb-3">
                            0
                        </div>

                        <div class="text-secondary">
                            Generadores activos
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">
                            Usuarios
                        </div>

                        <div class="h1 mb-3">
                            {{ \App\Models\User::count() }}
                        </div>

                        <div class="text-secondary">
                            Usuarios registrados
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">
                            Estado
                        </div>

                        <div class="h1 mb-3 text-success">
                            Activo
                        </div>

                        <div class="text-secondary">
                            Sistema funcionando
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
