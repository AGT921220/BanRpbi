<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessFinish
{
    private $isFinished;
    private $finishedById;
    private $finishedAt;
    public function __construct(
        int $isFinished,
        ?int $finishedById = null,
        ?string $finishedAt = null
    ) {
        $this->isFinished = $isFinished;
        $this->finishedById = $finishedById;
        $this->finishedAt = $finishedAt;
    }

    public function isFinished(): int
    {
        return $this->isFinished;
    }
    public function finishedById(): ?int
    {
        return $this->finishedById;
    }
    public function finishedAt(): ?string
    {
        return $this->finishedAt;
    }
}
