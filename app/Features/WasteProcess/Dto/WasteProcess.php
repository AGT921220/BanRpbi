<?php

namespace App\Features\WasteProcess\Dto;

use App\Models\WasteProcessType;
use Illuminate\Support\Carbon;

class WasteProcess
{

    private $wasteProcessHeader;
    private $params;
    private $details;
    public function __construct(WasteProcessHeader $wasteProcessHeader, array $params, array $details)
    {
        $this->wasteProcessHeader = $wasteProcessHeader;
        $this->params = $params;
        $this->details = $details;
    }

    public function toArray(): array
    {
        return [
            'waste_process_id' => $this->getWasteProcessId(),
            'date_start' => $this->getWasteProcessDate()->getDateStart(),
            'created_at' => $this->getWasteProcessDate()->getCreatedAt(),
            'machine_number' => $this->getWasteProcessMachine()->getMachineNumber(),
            'start_hour_machine' => $this->getWasteProcessMachine()->getStartHourMachine(),
            'hour_machine' => $this->getWasteProcessMachine()->getHourMachine(),
            'total_duration_hours' => $this->getTotalDurationHours(),
            'user_id' => $this->getWasteProcessHeader()->getUserId(),
            'waste_process_type_id' => $this->getWasteProcessHeader()->getWasteProcessTypeId(),
            'solution' => $this->getSolution(),
            'is_finished' => $this->getWasteProcessHeader()->getWasteProcessFinish()->isFinished(),
            'finished_by_id' => $this->getWasteProcessHeader()->getWasteProcessFinish()->finishedById(),
            'finished_at' => $this->getWasteProcessHeader()->getWasteProcessFinish()->finishedAt(),
            'params' => $this->getParams(),
            'details' => $this->getDetails()
        ];
    }
    private function getTotalDurationHours(): string
    {
        $start = Carbon::parse($this->getWasteProcessMachine()->getStartHourMachine());
        $end = Carbon::parse($this->getWasteProcessMachine()->getHourMachine());
        $diff = $start->diff($end);
        return $diff->format('%H:%I:%S');
    }
    public function getWasteProcessId(): int
    {
        return $this->wasteProcessHeader->getWasteProcessId();
    }
    public function getDateStart(): string
    {
        return $this->getWasteProcessDate()->getDateStart();
    }
    public function getWasteProcessTypeId(): int
    {
        return $this->wasteProcessHeader->getWasteProcessTypeId();
    }
    public function getHourStartMachine(): ?string
    {
        return $this->getWasteProcessMachine()->getStartHourMachine();
    }
    public function getHourMachine(): ?string
    {
        return $this->getWasteProcessMachine()->getHourMachine();
    }
    public function getMachineNumber(): ?int
    {
        return $this->getWasteProcessMachine()->getMachineNumber();
    }
    public function getParams(): array
    {
        return $this->params;
    }
    public function getDetails(): array
    {
        return $this->details;
    }




    public function getWasteProcessDate(): WasteProcessDate
    {
        return $this->wasteProcessHeader->getWasteProcessDate();
    }
    public function getWasteProcessMachine(): WasteProcessMachine
    {
        return $this->wasteProcessHeader->getWasteProcessMachine();
    }
    public function getWasteProcessHeader(): WasteProcessHeader
    {
        return $this->wasteProcessHeader;
    }
    public function getSolution(): ?string
    {
        return $this->wasteProcessHeader->getSolution();
    }
    public function getComments(): ?string
    {
        return $this->wasteProcessHeader->getComments();
    }
    public function isFinished(): bool
    {
        return $this->wasteProcessHeader->getWasteProcessFinish()->isFinished();
    }
    public function isFinishable(): bool
    {
        $processType = $this->getWasteProcessHeader()->getWasteProcessTypeId();
        $hourStartMachine = !!$this->getWasteProcessMachine()->getStartHourMachine();
        $hourEndMachine = !!$this->getWasteProcessMachine()->getHourMachine();
        $machineNumber = !!$this->getWasteProcessMachine()->getMachineNumber();
        $dateStart =  !!$this->getWasteProcessDate()->getDateStart();
        $solution = !!$this->getSolution();

        if ($processType == WasteProcessType::PROCESS_ESTERILIZACION) {
            return $this->isEsterilizacionFinishable(
                $hourStartMachine,
                $hourEndMachine,
                $machineNumber,
                $dateStart,
                $solution
            );
        }

        if ($processType == WasteProcessType::PROCESS_INCINERACION) {
            return $hourStartMachine && $dateStart && !$this->isFinished();
        }

        return false;
    }
    private function isEsterilizacionFinishable(
        bool $hourStartMachine,
        bool $hourEndMachine,
        bool $machineNumber,
        bool $dateStart,
        bool $solution
    ): bool {
        return $hourStartMachine && $hourEndMachine &&
            $machineNumber && $dateStart && $solution && !$this->isFinished();
    }
}
