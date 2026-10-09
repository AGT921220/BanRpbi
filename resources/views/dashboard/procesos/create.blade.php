@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header mb-2" style="display: flex;justify-content: space-between;">
                        <h1>Generar Proceso</h1>

                    </div>
                    <div class="card-body">

                        <form method="POST" action="/procesos" class="manifest_process_form" enctype="multipart/form-data">
                            @csrf



                            <div class="form-group">
                                <label>Fecha de Inicio</label>
                                <input type="date" class="form-control" value="{{ $fechaProcess }}" name="date_start"
                                    required>
                            </div>


                            <div class="form-group">
                                <div class="col-md-12">
                                    <label>Tipo de Procesos:</label>
                                    <select class="form-control waste_process_type_id" name="waste_process_type_id"
                                        id="waste_process_type_id" >
                                        @foreach ($processos as $process)
                                            <option value="{{ $process->id }}"> {{ $process->name }}
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="form-group">
                                <label>Manifiestos(Ingresa los # de Manifiestos separados por una coma) 1,77,22,96</label>
                                <input type="text" class="form-control manualManifestIds" value="">
                                <button type="button" class="btn btn-primary manualManifestIdsBtn">Agregar</button>
                            </div>


                            <h4>Manifiestos</h4>
                            <table class="table" style="overflow-x:scroll">
                                <thead>
                                    <tr>
                                        <th style="text-align: center" scope="col">Seleccionar</th>
                                        <th style="text-align: center" scope="col">No. Manifiesto</th>
                                        <th style="text-align: center" scope="col">No. Detalle</th>
                                        <th style="text-align: center" scope="col">No. Preinventario</th>
                                        <th style="text-align: center" scope="col">Cliente</th>
                                        <th style="text-align: center" scope="col">Numero de Perfil</th>
                                        <th style="text-align: center" scope="col">Fraccion Arancelaria</th>
                                        <th style="text-align: center" scope="col">Nombre de Residuo</th>
                                        <th style="text-align: center" scope="col">RA</th>
                                        <th style="text-align: center" scope="col">Tipo de Contenedor</th>
                                        <th style="text-align: center" scope="col">Cantidad</th>
                                        <th style="text-align: center" scope="col">Peso/Volumen(NETO)</th>
                                        <th style="text-align: center" scope="col">Unidad(Kgs./L)</th>
                                    </tr>

                                </thead>
                                <tbody style="text-align: center" class="tbody_details">
                                    <tr class="manifest_item">
                                    </tr>
                                </tbody>
                            </table>

                            <input type="hidden" name="manifest_ids" id="manifestInput">
                            <input type="hidden" name="detail_manifest_ids" id="manifestDetailInput">


                            <button class="btn btn-primary btn-block submit_manifest_btn floatCreateBtn" type="button" data-toggle="modal"
                                data-target="#myModal">Crear Proceso</button>
                        </form>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document" style="width: auto ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Manifiestos Seleccionados</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body manifest_added">
                    <table class="table table_manifest_added" style="overflow-x:scroll">
                        <thead>
                            <tr>
                                <th style="text-align: center" scope="col">No. Manifiesto</th>
                                <th style="text-align: center" scope="col">No. Detalle</th>
                                <th style="text-align: center" scope="col">No. Preinventario</th>
                                <th style="text-align: center" scope="col">Numero de Perfil</th>
                                <th style="text-align: center" scope="col">Fraccion Arancelaria</th>
                                <th style="text-align: center" scope="col">Nombre de Residuo</th>
                                <th style="text-align: center" scope="col">RA</th>
                                <th style="text-align: center" scope="col">Tipo de Contenedor</th>
                                <th style="text-align: center" scope="col">Cantidad</th>
                                <th style="text-align: center" scope="col">Peso/Volumen(NETO)</th>
                                <th style="text-align: center" scope="col">Unidad(Kgs./L)</th>
                            </tr>

                        </thead>
                        <tbody style="text-align: center" class="tbody_details_added">
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success submit_process_validate">Validar Manifiestos</button>

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary submit_process">Crear</button>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .check_manifest {
        min-width: 25px;
        min-height: 25px;
        cursor: pointer;
    }



    .manifest_item:has(.check_manifest:checked) {
        background-color: #009300 !important;
    }

    /* .manifest_item.checked {
    background-color: #009300 !important;
} */
</style>
<script src="{{ asset(mix('js/procesos/create.js')) }}" defer></script>
<script src="{{ asset(mix('js/registro.js')) }}" defer></script>
