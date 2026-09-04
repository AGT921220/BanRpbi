<?php

namespace App\Features\RpbiProfiles\Domain;

final readonly class RpbiProfile
{
    public function __construct(
        private int $id,
        private string $code,
        private string $name
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): string
    {
        $default = match ($this->code) {
            'BI1' => 'flask-outline',
            'BI2' => 'needle',
            'BI3' => 'biohazard',
            'BI4' => 'trash-can-outline',
            'BI5' => 'water-outline',
            default => 'help-circle-outline',
        };

        return config(
            "rpbi.profiles.{$this->code}.icon",
            $default
        );
    }

    public function getColor(): string
    {
        $default = match ($this->code) {
            'BI1' => '#EF4444',
            'BI2' => '#F59E0B',
            'BI3' => '#10B981',
            'BI4' => '#3B82F6',
            'BI5' => '#DC2626',
            default => '#64748B',
        };

        return config(
            "rpbi.profiles.{$this->code}.color",
            $default
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
        ];
    }
}