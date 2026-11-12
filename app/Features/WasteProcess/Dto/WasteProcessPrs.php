<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessPrs
{
    public function __construct(
        private ?string $tempCamaraPrimaria = null,
        private ?string $tempCamaraSecundaria = null,
        private ?string $tempEntradaVenturi = null,
        private ?string $tempSalidaVenturi = null,
        private ?string $tempSalidaChimenea = null,
        private ?string $oxigeno = null,
        private ?string $presionNegativa = null,
        private ?string $consumoGasLp = null,
        private ?string $consumoPromedioAgua = null,
        private ?string $evaporacionAguaTratada = null,
        private ?string $patologicos = null,
        private ?string $cultivosCepas = null,
        private ?string $sangre = null,
        private ?string $cenizaGenerada = null,
        private ?string $descargaLavador = null,
        private ?string $clEnRps = null,
        private ?string $coPpm = null,
        private ?string $radiacionResiduos = null,
        private ?string $solidosMixtos = null,
        private ?string $rpSolidosLodos = null,
        private ?string $medicamentosCaducos = null,
    ) {}

    /**
     * @return array<string, ?string>
     */
    public function attributes(): array
    {
        return [
            'temp_camara_primaria' => $this->tempCamaraPrimaria,
            'temp_camara_secundaria' => $this->tempCamaraSecundaria,
            'temp_entrada_venturi' => $this->tempEntradaVenturi,
            'temp_salida_venturi' => $this->tempSalidaVenturi,
            'temp_salida_chimenea' => $this->tempSalidaChimenea,
            'oxigeno' => $this->oxigeno,
            'presion_negativa' => $this->presionNegativa,
            'cl_en_rps' => $this->clEnRps,
            'co_ppm' => $this->coPpm,
            'radiacion_residuos' => $this->radiacionResiduos,
            'consumo_gas_lp' => $this->consumoGasLp,
            'consumo_promedio_agua' => $this->consumoPromedioAgua,
            'evaporacion_agua_tratada' => $this->evaporacionAguaTratada,
            'solidos_mixtos' => $this->solidosMixtos,
            'rp_solidos_lodos' => $this->rpSolidosLodos,
            'medicamentos_caducos' => $this->medicamentosCaducos,
            'patologicos' => $this->patologicos,
            'cultivos_cepas' => $this->cultivosCepas,
            'sangre' => $this->sangre,
            'ceniza_generada' => $this->cenizaGenerada,
            'descarga_lavador' => $this->descargaLavador,
        ];
    }
}
