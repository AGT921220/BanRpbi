<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessPrar
{
    private $cantidadSolucionReciclar;
    private $sosaCaustica;
    private $sulfactanteC500;
    private $solucionClarifT42;
    private $solucionFlocl146;
    private $ingresoFiltroPrensa;
    private $acido;
    private $phInicial;
    private $phFinal;
    private $phLodos;
    private $eca12;
    private $eca22;
    private $eca1350;
    private $eca49;
    private $eca20pa;
    private $lodosGeneradosPorcentaje;
    private $lodosGeneradosTnk;
    private $lodosGeneradosDisposicion;
    private $aguaRetornoProcesoFiltroPrensa;
    private $cantidadTotalAguaReciclada;
    private $others;

    public function __construct(
        ?string $cantidadSolucionReciclar = null,
        ?string $sosaCaustica = null,
        ?string $sulfactanteC500 = null,
        ?string $solucionClarifT42 = null,
        ?string $solucionFlocl146 = null,
        ?string $ingresoFiltroPrensa = null,
        ?string $acido = null,
        ?string $phInicial = null,
        ?string $phFinal = null,
        ?string $phLodos = null,
        ?string $eca12 = null,
        ?string $eca22 = null,
        ?string $eca1350 = null,
        ?string $eca49 = null,
        ?string $eca20pa = null,
        ?string $lodosGeneradosPorcentaje = null,
        ?string $lodosGeneradosTnk = null,
        ?string $lodosGeneradosDisposicion = null,
        ?string $aguaRetornoProcesoFiltroPrensa = null,
        ?string $cantidadTotalAguaReciclada = null,
        ?string $others = null
    ) {
        $this->cantidadSolucionReciclar = $cantidadSolucionReciclar;
        $this->sosaCaustica = $sosaCaustica;
        $this->sulfactanteC500 = $sulfactanteC500;
        $this->solucionClarifT42 = $solucionClarifT42;
        $this->solucionFlocl146 = $solucionFlocl146;
        $this->ingresoFiltroPrensa = $ingresoFiltroPrensa;
        $this->acido = $acido;
        $this->phInicial = $phInicial;
        $this->phFinal = $phFinal;
        $this->phLodos = $phLodos;
        $this->eca12 = $eca12;
        $this->eca22 = $eca22;
        $this->eca1350 = $eca1350;
        $this->eca49 = $eca49;
        $this->eca20pa = $eca20pa;
        $this->lodosGeneradosPorcentaje = $lodosGeneradosPorcentaje;
        $this->lodosGeneradosTnk = $lodosGeneradosTnk;
        $this->lodosGeneradosDisposicion = $lodosGeneradosDisposicion;
        $this->aguaRetornoProcesoFiltroPrensa = $aguaRetornoProcesoFiltroPrensa;
        $this->cantidadTotalAguaReciclada = $cantidadTotalAguaReciclada;
        $this->others = $others;
    }

    public function getCantidadSolucionReciclar():?string
    {
        return $this->cantidadSolucionReciclar;
    }

    public function getSosaCaustica():?string
    {
        return $this->sosaCaustica;
    }

    public function getSulfactanteC500():?string
    {
        return $this->sulfactanteC500;
    }

    public function getSolucionClarifT42():?string
    {
        return $this->solucionClarifT42;
    }

    public function getSolucionFlocl146():?string
    {
        return $this->solucionFlocl146;
    }

    public function getIngresoFiltroPrensa():?string
    {
        return $this->ingresoFiltroPrensa;
    }

    public function getAcido():?string
    {
        return $this->acido;
    }

    public function getPhInicial():?string
    {
        return $this->phInicial;
    }

    public function getPhFinal():?string
    {
        return $this->phFinal;
    }

    public function getPhLodos():?string
    {
        return $this->phLodos;
    }

    public function getEca12():?string
    {
        return $this->eca12;
    }

    public function getEca22():?string
    {
        return $this->eca22;
    }

    public function getEca1350():?string
    {
        return $this->eca1350;
    }

    public function getEca49():?string
    {
        return $this->eca49;
    }

    public function getEca20pa():?string
    {
        return $this->eca20pa;
    }

    public function getLodosGeneradosPorcentaje():?string
    {
        return $this->lodosGeneradosPorcentaje;
    }

    public function getLodosGeneradosTnk():?string
    {
        return $this->lodosGeneradosTnk;
    }

    public function getLodosGeneradosDisposicion():?string
    {
        return $this->lodosGeneradosDisposicion;
    }

    public function getAguaRetornoProcesoFiltroPrensa():?string
    {
        return $this->aguaRetornoProcesoFiltroPrensa;
    }

    public function getCantidadTotalAguaReciclada():?string
    {
        return $this->cantidadTotalAguaReciclada;
    }
    public function getOthers():?string
    {
        return $this->others;
    }
}
