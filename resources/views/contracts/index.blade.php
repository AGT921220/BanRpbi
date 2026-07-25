@extends('layouts.app')

@section('title', 'Contratos')
@section('page-title', 'Contratos')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::CONTRACTS_CREATE)
        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>
            Nuevo contrato
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-file-description me-2"></i>
                    Catálogo de contratos
                </h3>
            </div>

            <div class="card-body border-bottom py-3">
                <form method="GET" action="{{ route('contracts.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label" for="contracts-search">Buscar</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>
                            <input
                                type="search"
                                name="search"
                                id="contracts-search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="Nombre, notas o frecuencia"
                            >
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>
                            Buscar
                        </button>
                    </div>
                    @if (request()->filled('search'))
                        <div class="col-auto">
                            <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>
                                Limpiar
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Duración</th>
                            <th>Frecuencia</th>
                            <th>Notas</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $contract)
                            @php
                                $frequencyLabels = \App\Models\Contract::frequencyLabels();
                            @endphp
                            <tr>
                                <td>{{ $contract->name }}</td>
                                <td>{{ $contract->duration_months }} meses</td>
                                <td>
                                    {{ $frequencyLabels[$contract->frequency] ?? $contract->frequency }}
                                </td>
                                <td class="text-secondary">
                                    {{ \Illuminate\Support\Str::limit($contract->notes ?? '—', 60) }}
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @can(\App\Features\Permissions\Constants\PermissionTypes::CONTRACTS_UPDATE)
                                            <a
                                                href="{{ route('contracts.edit', $contract) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i class="ti ti-pencil me-1"></i>
                                                Editar
                                            </a>
                                        @endcan

                                        @can(\App\Features\Permissions\Constants\PermissionTypes::CONTRACTS_DELETE)
                                            <form
                                                action="{{ route('contracts.destroy', $contract) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar este contrato del catálogo?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash me-1"></i>
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    No hay contratos en el catálogo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($contracts->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
