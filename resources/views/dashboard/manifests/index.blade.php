@extends('layouts.app')

@section('title', 'Manifiestos')
@section('page-title', 'Manifiestos')

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-file-certificate me-2"></i>
                    Listado de manifiestos
                </h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table
                        id="manifests-table"
                        class="table table-vcenter card-table w-100"
                        data-url="{{ route('manifest-headers.index') }}"
                    >
                        <thead>
                            <tr>
                                <th>N° Manifiesto</th>
                                <th>Status</th>
                                <th>Cliente</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/modules/manifests/index.js')
@endpush
