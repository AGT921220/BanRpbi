<?php

namespace App\Features\WasteProcess\Dto;

class WasteProcessDate
{
    private $dateStart;
    private $createdAt;
    public function __construct(string $dateStart, string $createdAt)
    {
        $this->dateStart = $dateStart;
        $this->createdAt = $createdAt;
    }
    public function getDateStart(): string
    {
        return $this->dateStart;
    }
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
