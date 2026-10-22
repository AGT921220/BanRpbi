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
            'temperatura_1' => ['nullable', 'numeric'],
            'temperatura_2' => ['nullable', 'numeric'],
            'temperatura_3' => ['nullable', 'numeric'],
            'cantidad_ingresado' => ['nullable', 'numeric'],
            'cantidad_recuperados' => ['nullable', 'numeric'],
            'porcentaje_recuperado' => ['nullable', 'numeric'],
            'cantidad_sedimentos' => ['nullable', 'numeric'],
            'porcentaje_sedimentos' => ['nullable', 'numeric'],
            'factor_consumo_kw' => ['nullable', 'numeric'],
            'consumo_kw_hrs' => ['nullable', 'numeric'],
            'costo_kw_hr' => ['nullable', 'numeric'],
            'costo_total_operacion' => ['nullable', 'numeric'],
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
