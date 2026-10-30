<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessPrar
{
    public function __construct(
        private ?string $tempEsterilizador = null,
        private ?string $presionCamaraPrincipal = null,
        private ?string $eliminacionBiologica = null,
        private ?string $cicloCarga = null,
        private ?string $rpbiBi1Cepas = null,
        private ?string $rpbiBi2Punzocortantes = null,
        private ?string $rpbiBi4NoAnatomicos = null,
        private ?string $rpbiBi5Sangre = null,
        private ?string $totalProcesada = null,
    ) {}

    /**
     * @return array<string, ?string>
     */
    public function attributes(): array
    {
        return [
            'temp_esterilizador' => $this->tempEsterilizador,
            'presion_camara_principal' => $this->presionCamaraPrincipal,
            'eliminacion_biologica' => $this->eliminacionBiologica,
            'ciclo_carga' => $this->cicloCarga,
            'rpbi_bi1_cepas' => $this->rpbiBi1Cepas,
            'rpbi_bi2_punzocortantes' => $this->rpbiBi2Punzocortantes,
            'rpbi_bi4_no_anatomicos' => $this->rpbiBi4NoAnatomicos,
            'rpbi_bi5_sangre' => $this->rpbiBi5Sangre,
            'total_procesada' => $this->totalProcesada,
        ];
    }
}
