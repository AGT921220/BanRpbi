<?php

namespace App\Features\RpbiProfiles\Application;

use Illuminate\Support\Collection;

final class BuildRpbiProfilesConfig
{
    public function __invoke(Collection $rpbiProfiles): Collection
    {
        return $rpbiProfiles->map(function ($profile) {
            $config = $this->config()[$profile->code] ?? [];

            return [
                'id' => $profile->id,
                'code' => $profile->code,
                'name' => $profile->name,
                'icon' => $config['icon'] ?? 'help-circle-outline',
                'color' => $config['color'] ?? '#64748B',
            ];
        });
    }

    private function config(): array
    {
        return [
            'BI1' => [
                'icon' => 'flask-outline',
                'color' => '#EF4444',
            ],
            'BI2' => [
                'icon' => 'needle',
                'color' => '#F59E0B',
            ],
            'BI3' => [
                'icon' => 'biohazard',
                'color' => '#10B981',
            ],
            'BI4' => [
                'icon' => 'trash-can-outline',
                'color' => '#3B82F6',
            ],
            'BI5' => [
                'icon' => 'water-outline',
                'color' => '#DC2626',
            ],
        ];
    }
}