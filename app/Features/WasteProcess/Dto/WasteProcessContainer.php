<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessContainer
{
    private $cantidadContenedores;
    private $cantidadContenedoresProcesoInterno;
    private $aguaRecicladaPresion;
    private $contenedoresRecuperados;
    private $contenedoresInutilizables;
    private $aguaResidualProceso;
    private $lodosGeneradosProceso;
    private $solidosGeneradosProceso;
    private $psi;

    public function __construct(
        ?string $cantidadContenedores = null,
        ?string $cantidadContenedoresProcesoInterno = null,
        ?string $aguaRecicladaPresion = null,
        ?string $contenedoresRecuperados = null,
        ?string $contenedoresInutilizables = null,
        ?string $aguaResidualProceso = null,
        ?string $lodosGeneradosProceso = null,
        ?string $solidosGeneradosProceso = null,
        ?string $psi = null
    ) {
        $this->cantidadContenedores = $cantidadContenedores;
        $this->cantidadContenedoresProcesoInterno = $cantidadContenedoresProcesoInterno;
        $this->aguaRecicladaPresion = $aguaRecicladaPresion;
        $this->contenedoresRecuperados = $contenedoresRecuperados;
        $this->contenedoresInutilizables = $contenedoresInutilizables;
        $this->aguaResidualProceso = $aguaResidualProceso;
        $this->lodosGeneradosProceso = $lodosGeneradosProceso;
        $this->solidosGeneradosProceso = $solidosGeneradosProceso;
        $this->psi = $psi;
    }
    public function toArray()
    {
        return
            [
                'cantidadContenedores' => $this->cantidadContenedores,
                'cantidadContenedoresProcesoInterno' => $this->cantidadContenedoresProcesoInterno,
                'aguaRecicladaPresion' => $this->aguaRecicladaPresion,
                'contenedoresRecuperados' => $this->contenedoresRecuperados,
                'contenedoresInutilizables' => $this->contenedoresInutilizables,
                'aguaResidualProceso' => $this->aguaResidualProceso,
                'lodosGeneradosProceso' => $this->lodosGeneradosProceso,
                'solidosGeneradosProceso' => $this->solidosGeneradosProceso,
            ];
    }

    public function getCantidadContenedores(): ?string
    {
        return $this->cantidadContenedores;
    }

    public function getCantidadContenedoresProcesoInterno(): ?string
    {
        return $this->cantidadContenedoresProcesoInterno;
    }

    public function getAguaRecicladaPresion(): ?string
    {
        return $this->aguaRecicladaPresion;
    }

    public function getContenedoresRecuperados(): ?string
    {
        return $this->contenedoresRecuperados;
    }

    public function getContenedoresInutilizables(): ?string
    {
        return $this->contenedoresInutilizables;
    }

    public function getAguaResidualProceso(): ?string
    {
        return $this->aguaResidualProceso;
    }

    public function getLodosGeneradosProceso(): ?string
    {
        return $this->lodosGeneradosProceso;
    }

    public function getSolidosGeneradosProceso(): ?string
    {
        return $this->solidosGeneradosProceso;
    }
    public function getPsi(): ?string
    {
        return $this->psi;
    }
}
