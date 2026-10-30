<?php

namespace App\Http\Requests\Process;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWasteProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_start' => ['required', 'date'],
            'hour_start_machine' => ['nullable', 'date_format:H:i'],
            'hour_machine' => ['nullable', 'date_format:H:i'],
            'machine_number' => ['nullable', 'integer', 'exists:reactors,id'],
            'solution' => ['nullable', 'string', 'max:255'],
            'comments' => ['nullable', 'string'],
            'finish' => ['nullable', 'boolean'],
            'details' => ['nullable', 'array'],
            'details.*.id' => ['required_with:details', 'integer'],
            'details.*.value' => ['required_with:details', 'numeric', 'gt:0'],
            'temp_camara_primaria' => ['nullable', 'numeric'],
            'temp_camara_secundaria' => ['nullable', 'numeric'],
            'temp_entrada_venturi' => ['nullable', 'numeric'],
            'temp_salida_venturi' => ['nullable', 'numeric'],
            'temp_salida_chimenea' => ['nullable', 'numeric'],
            'oxigeno' => ['nullable', 'numeric'],
            'presion_negativa' => ['nullable', 'numeric'],
            'consumo_gas_lp' => ['nullable', 'numeric'],
            'consumo_promedio_agua' => ['nullable', 'numeric'],
            'evaporacion_agua_tratada' => ['nullable', 'numeric'],
            'patologicos' => ['nullable', 'numeric'],
            'cultivos_cepas' => ['nullable', 'numeric'],
            'sangre' => ['nullable', 'numeric'],
            'ceniza_generada' => ['nullable', 'numeric'],
            'descarga_lavador' => ['nullable', 'numeric'],
            'temp_esterilizador' => ['nullable', 'numeric'],
            'presion_camara_principal' => ['nullable', 'numeric'],
            'eliminacion_biologica' => ['nullable', 'string', 'max:255'],
            'ciclo_carga' => ['nullable', 'string', 'max:255'],
            'rpbi_bi1_cepas' => ['nullable', 'numeric'],
            'rpbi_bi2_punzocortantes' => ['nullable', 'numeric'],
            'rpbi_bi4_no_anatomicos' => ['nullable', 'numeric'],
            'rpbi_bi5_sangre' => ['nullable', 'numeric'],
            'total_procesada' => ['nullable', 'numeric'],
        ];
    }
}
