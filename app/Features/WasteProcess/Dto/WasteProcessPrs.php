<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessPrs
{
    private $cantidadIngresado;
    private $temperatura1;
    private $temperatura2;
    private $temperatura3;
    private $cantidadRecuperados;
    private $porcentajeRecuperado;
    private $cantidadSedimentos;
    private $porcentajeSedimentos;
    private $factorConsumoKw;
    private $consumoKwHrs;
    private $costoKwHr;
    private $costoTotalOperacion;

    public function __construct(
        ?string $cantidadIngresado = null,
        ?string $temperatura1 = null,
        ?string $temperatura2 = null,
        ?string $temperatura3 = null,
        ?string $cantidadRecuperados = null,
        ?string $porcentajeRecuperado = null,
        ?string $cantidadSedimentos = null,
        ?string $porcentajeSedimentos = null,
        ?string $factorConsumoKw = null,
        ?string $consumoKwHrs = null,
        ?string $costoKwHr = null,
        ?string $costoTotalOperacion = null
    ) {
        $this->cantidadIngresado = $cantidadIngresado;
        $this->temperatura1 = $temperatura1;
        $this->temperatura2 = $temperatura2;
        $this->temperatura3 = $temperatura3;
        $this->cantidadRecuperados = $cantidadRecuperados;
        $this->porcentajeRecuperado = $porcentajeRecuperado;
        $this->cantidadSedimentos = $cantidadSedimentos;
        $this->porcentajeSedimentos = $porcentajeSedimentos;
        $this->factorConsumoKw = $factorConsumoKw;
        $this->consumoKwHrs = $consumoKwHrs;
        $this->costoKwHr = $costoKwHr;
        $this->costoTotalOperacion = $costoTotalOperacion;
    }

    public function toArray()
    {
        return [
            'cantidadIngresado' => $this->cantidadIngresado,
            'temperatura1' => $this->temperatura1,
            'temperatura2' => $this->temperatura2,
            'temperatura3' => $this->temperatura3,
            'cantidadRecuperados' => $this->cantidadRecuperados,
            'porcentajeRecuperado' => $this->porcentajeRecuperado,
            'cantidadSedimentos' => $this->cantidadSedimentos,
            'porcentajeSedimentos' => $this->porcentajeSedimentos,
            'factorConsumoKw' => $this->factorConsumoKw,
            'consumoKwHrs' => $this->consumoKwHrs,
            'costoKwHr' => $this->costoKwHr,
            'costoTotalOperacion' => $this->costoTotalOperacion,
        ];
    }

    public function getCantidadIngresado(): ?string
    {
        return $this->cantidadIngresado;
    }

    public function getTemperatura1(): ?string
    {
        return $this->temperatura1;
    }

    public function getTemperatura2(): ?string
    {
        return $this->temperatura2;
    }

    public function getTemperatura3(): ?string
    {
        return $this->temperatura3;
    }

    public function getCantidadRecuperados(): ?string
    {
        return $this->cantidadRecuperados;
    }

    public function getPorcentajeRecuperado(): ?string
    {
        return $this->porcentajeRecuperado;
    }

    public function getCantidadSedimentos(): ?string
    {
        return $this->cantidadSedimentos;
    }

    public function getPorcentajeSedimentos(): ?string
    {
        return $this->porcentajeSedimentos;
    }

    public function getFactorConsumoKw(): ?string
    {
        return $this->factorConsumoKw;
    }

    public function getConsumoKwHrs(): ?string
    {
        return $this->consumoKwHrs;
    }

    public function getCostoKwHr(): ?string
    {
        return $this->costoKwHr;
    }

    public function getCostoTotalOperacion(): ?string
    {
        return $this->costoTotalOperacion;
    }
}
