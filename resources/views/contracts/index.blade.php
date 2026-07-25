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
                    Listado de contratos
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
                                placeholder="Folio, nombre, cliente o empresa"
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
                            <th>Folio</th>
                            <th>Nombre</th>
                            <th>Cliente</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $contract)
                            <tr>
                                <td>{{ $contract->folio }}</td>
                                <td>{{ $contract->name }}</td>
                                <td>
                                    {{ $contract->client?->name }}
                                    {{ $contract->client?->parentarl_surname }}
                                    @if ($contract->client?->company)
                                        <div class="text-secondary small">{{ $contract->client->company }}</div>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    {{ $contract->starts_at?->format('d/m/Y') }}
                                    —
                                    {{ $contract->ends_at?->format('d/m/Y') }}
                                </td>
                                <td>
                                    @php
                                        $statusLabels = \App\Models\Contract::statusLabels();
                                        $badge = match ($contract->status) {
                                            'active' => 'bg-green-lt',
                                            'draft' => 'bg-blue-lt',
                                            'expired' => 'bg-yellow-lt',
                                            'cancelled' => 'bg-red-lt',
                                            default => 'bg-secondary-lt',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">
                                        {{ $statusLabels[$contract->status] ?? $contract->status }}
                                    </span>
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
                                                onsubmit="return confirm('¿Eliminar este contrato?')"
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
                                <td colspan="6" class="text-center text-secondary py-4">
                                    No hay contratos registrados.
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
