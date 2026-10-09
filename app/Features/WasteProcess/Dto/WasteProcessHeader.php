<?php

namespace App\Features\WasteProcess\Dto;

use App\Models\WasteProcessType;

class WasteProcessHeader
{
    private $wasteProcessId;
    private $wasteProcessDate;
    private $wasteProcessMachine;
    private $userId;
    private $wasteProcessTypeId;
    private $solution;
    private $wasteProcessFinish;
    private $comments;
    public function __construct(
        int $wasteProcessId,
        WasteProcessDate $wasteProcessDate,
        WasteProcessMachine $wasteProcessMachine,
        WasteProcessFinish $wasteProcessFinish,
        int $userId,
        int $wasteProcessTypeId,
        ?string $solution = null,
        ?string $comments = null
    ) {
        $this->wasteProcessId = $wasteProcessId;
        $this->wasteProcessDate = $wasteProcessDate;
        $this->wasteProcessMachine = $wasteProcessMachine;
        $this->wasteProcessFinish = $wasteProcessFinish;
        $this->userId = $userId;
        $this->wasteProcessTypeId = $wasteProcessTypeId;
        $this->solution = $solution;
        $this->comments = $comments;
    }

    public function getWasteProcessId(): int
    {
        return $this->wasteProcessId;
    }
    public function getWasteProcessDate(): WasteProcessDate
    {
        return $this->wasteProcessDate;
    }
    public function getWasteProcessMachine(): WasteProcessMachine
    {
        return $this->wasteProcessMachine;
    }
    public function getWasteProcessFinish(): WasteProcessFinish
    {
        return $this->wasteProcessFinish;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getWasteProcessTypeId(): int
    {
        return $this->wasteProcessTypeId;
    }
    public function getWasteProcessName(): string
    {

        if ($this->wasteProcessTypeId == WasteProcessType::PROCESS_INCINERACION) {
            return 'Incineración';
        }

        if ($this->wasteProcessTypeId == WasteProcessType::PROCESS_ESTERILIZACION) {
            return 'Esterilización';
        }

        return '';
    }
    public function getSolution(): ?string
    {
        return $this->solution;
    }
    public function getComments(): ?string
    {
        return $this->comments;
    }
    // public function getDateStart(): string
    // {
    //     return $this->wasteProcessDate->getDateStart();
    // }
    // public function getCreatedAt(): string
    // {
    //     return $this->wasteProcessDate->getCreatedAt();
    // }
    // public function getMachineNumber(): ?int
    // {
    //     return $this->wasteProcessMachine->getMachineNumber();
    // }
    // public function getStartHourMachine(): ?string
    // {
    //     return $this->wasteProcessMachine->getStartHourMachine();
    // }
    // public function getHourMachine(): ?string
    // {
    //     return $this->wasteProcessMachine->getHourMachine();
    // }
}
