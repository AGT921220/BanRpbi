@extends('layouts.app')

@section('title', 'Proceso #'.$process->id)
@section('page-title', 'Proceso #'.$process->id)

@section('page-actions')
    <a href="{{ route('procesos.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i>
        Listado
    </a>
    @unless ($process->is_finished)
        <a href="{{ route('procesos.edit', $process->id) }}" class="btn btn-primary">
            <i class="ti ti-pencil me-1"></i>
            Continuar captura
        </a>
    @endunless
@endsection

@section('content')
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumen</h3>
                    </div>
                    <div class="card-body">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Tipo</div>
                                <div class="datagrid-content">{{ $process->type?->name ?? '—' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Fecha</div>
                                <div class="datagrid-content">{{ $process->date_start }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Estado</div>
                                <div class="datagrid-content">
                                    {{ $process->is_finished ? 'Cerrado' : 'Abierto' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Líneas</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Manifiesto</th>
                                    <th>Perfil</th>
                                    <th>Disponible</th>
                                    <th>Capturado</th>
                                    <th>Remanente</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($process->details as $detail)
                                    @php
                                        $available = $balance->availableWeight((int) $detail->service_detail_id);
                                        $treated = $balance->treatedWeight((int) $detail->service_detail_id);
                                        $remaining = $balance->remainingWeight((int) $detail->service_detail_id);
                                    @endphp
                                    <tr>
                                        <td>#{{ $detail->manifest_id }}</td>
                                        <td>{{ $detail->serviceDetail?->rpbiProfile?->name ?? '—' }}</td>
                                        <td>{{ number_format($available, 2) }}</td>
                                        <td>{{ number_format($treated, 2) }}</td>
                                        <td>{{ number_format($remaining, 2) }}</td>
                                        <td>
                                            @if ($detail->is_completed)
                                                Agotada
                                            @elseif ($detail->is_finished)
                                                Parcial
                                            @else
                                                Pendiente
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Certificados / folios</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($process->certificates as $certificate)
                            <div class="list-group-item">
                                Cliente #{{ $certificate->client_id }}
                                · Cert #{{ $certificate->id }}
                                @foreach ($certificate->folios as $folio)
                                    <div class="text-secondary small">Folio #{{ $folio->id }}</div>
                                @endforeach
                            </div>
                        @empty
                            <div class="list-group-item text-secondary">Sin certificados.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
