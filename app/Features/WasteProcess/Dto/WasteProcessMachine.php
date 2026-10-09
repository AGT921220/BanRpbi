<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessMachine
{

    public ?string $startHourMachine;
    public ?string $hourMachine;
    public ?int $machineNumber;

    public function __construct(?int $machineNumber = null, string $startHourMachine = null, string $hourMachine = null)
    {
        $this->startHourMachine = $startHourMachine;
        $this->hourMachine = $hourMachine;
        $this->machineNumber = $machineNumber;
    }
    public function getStartHourMachine(): ?string
    {
        return $this->startHourMachine;
    }
    public function getHourMachine(): ?string
    {
        return $this->hourMachine;
    }
    public function getMachineNumber(): ?int
    {
        return $this->machineNumber;
    }
}
