@extends('layouts.app')

@section('title', 'Procesos')
@section('page-title', 'Procesos RPBI')

@section('page-actions')
    <a href="{{ route('procesos.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i>
        Nuevo proceso
    </a>
@endsection

@section('content')
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-flame me-2"></i>
                    Listado de procesos
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="w-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($processes as $process)
                            <tr>
                                <td>{{ $process->id }}</td>
                                <td>{{ $process->type?->name ?? '—' }}</td>
                                <td>{{ optional($process->date_start)->format('d/m/Y') ?? $process->date_start }}</td>
                                <td>
                                    @if ($process->is_finished)
                                        <span class="badge bg-success-lt">Cerrado</span>
                                    @else
                                        <span class="badge bg-warning-lt">Abierto</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('procesos.show', $process->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-eye me-1"></i>
                                            Ver
                                        </a>
                                        @unless ($process->is_finished)
                                            <a href="{{ route('procesos.edit', $process->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-pencil me-1"></i>
                                                Capturar
                                            </a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    No hay procesos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($processes->hasPages())
                <div class="card-footer">{{ $processes->links() }}</div>
            @endif
        </div>
    </div>
@endsection
