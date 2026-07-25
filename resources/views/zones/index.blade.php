@extends('layouts.app')

@section('title', 'Zonas')
@section('page-title', 'Zonas')

@section('page-actions')
    @can(\App\Features\Permissions\Constants\PermissionTypes::ZONES_CREATE)
        <a href="{{ route('zones.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>
            Nueva zona
        </a>
    @endcan
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-map-2 me-2"></i>
                    Listado de zonas
                </h3>
            </div>

            <div class="card-body border-bottom py-3">
                <form method="GET" action="{{ route('zones.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label" for="zones-search">Buscar</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>
                            <input
                                type="search"
                                name="search"
                                id="zones-search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="Nombre o descripción"
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
                            <a href="{{ route('zones.index') }}" class="btn btn-outline-secondary">
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
                            <th>Descripción</th>
                            <th>Color</th>
                            <th>Estado</th>
                            <th>Actualizado</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($zones as $zone)
                            <tr>
                                <td>{{ $zone->name }}</td>
                                <td class="text-secondary">
                                    {{ \Illuminate\Support\Str::limit($zone->description ?? '—', 60) }}
                                </td>
                                <td>
                                    <span
                                        class="avatar avatar-xs"
                                        style="background-color: {{ $zone->color ?: '#206bc4' }};"
                                        title="{{ $zone->color ?: '#206bc4' }}"
                                    ></span>
                                    <span class="ms-1 text-secondary">{{ $zone->color ?: '—' }}</span>
                                </td>
                                <td>
                                    @if ($zone->is_active)
                                        <span class="badge bg-green-lt">Activa</span>
                                    @else
                                        <span class="badge bg-secondary-lt">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    {{ $zone->updated_at?->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        @can(\App\Features\Permissions\Constants\PermissionTypes::ZONES_UPDATE)
                                            <a
                                                href="{{ route('zones.edit', $zone) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i class="ti ti-pencil me-1"></i>
                                                Editar
                                            </a>

                                            <form
                                                action="{{ route('zones.toggle-status', $zone) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="ti ti-toggle-{{ $zone->is_active ? 'right' : 'left' }} me-1"></i>
                                                    {{ $zone->is_active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endcan

                                        @can(\App\Features\Permissions\Constants\PermissionTypes::ZONES_DELETE)
                                            <form
                                                action="{{ route('zones.destroy', $zone) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar esta zona?')"
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
                                    No hay zonas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($zones->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $zones->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
