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
            'acido' => ['nullable', 'numeric'],
            'sosa_caustica' => ['nullable', 'numeric'],
            'eca_12' => ['nullable', 'numeric'],
            'eca_22' => ['nullable', 'numeric'],
            'eca_1350' => ['nullable', 'numeric'],
            'eca_49' => ['nullable', 'numeric'],
            'eca_20pa' => ['nullable', 'numeric'],
            'sulfactante_c500' => ['nullable', 'numeric'],
            'solucion_clarif_t42' => ['nullable', 'numeric'],
            'solucion_flocl_146' => ['nullable', 'numeric'],
            'ph_inicial' => ['nullable', 'numeric'],
            'ph_final' => ['nullable', 'numeric'],
            'ph_lodos' => ['nullable', 'numeric'],
            'lodos_generados_porcentaje' => ['nullable', 'numeric'],
            'lodos_generados_tnk_lodos' => ['nullable', 'numeric'],
            'ingreso_filtro_prensa' => ['nullable', 'numeric'],
            'lodos_generados_disposicion' => ['nullable', 'numeric'],
            'agua_retorno_proceso_filtro_prensa' => ['nullable', 'numeric'],
            'cantidad_total_agua_reciclada' => ['nullable', 'numeric'],
            'cantidad_solucion_reciclar' => ['nullable', 'numeric'],
            'others' => ['nullable', 'numeric'],
        ];
    }
}
