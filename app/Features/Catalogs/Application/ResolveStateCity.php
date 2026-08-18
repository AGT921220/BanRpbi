<?php

namespace App\Features\Catalogs\Application;

use App\Models\City;
use App\Models\State;
use Illuminate\Support\Str;

final class ResolveStateCity
{
    /**
     * @return array{state_id: int|null, city_id: int|null, state_found: bool, city_found: bool}
     */
    public function __invoke(?string $stateName, ?string $cityName): array
    {
        $stateName = trim((string) $stateName);
        $cityName = trim((string) $cityName);

        if ($stateName === '' && $cityName === '') {
            return [
                'state_id' => null,
                'city_id' => null,
                'state_found' => true,
                'city_found' => true,
            ];
        }

        $state = $stateName !== '' ? $this->findState($stateName) : null;
        $city = ($cityName !== '' && $state !== null)
            ? $this->findCity((int) $state->id, $cityName)
            : null;

        return [
            'state_id' => $state?->id,
            'city_id' => $city?->id,
            'state_found' => $stateName === '' || $state !== null,
            'city_found' => $cityName === '' || $city !== null,
        ];
    }

    private function findState(string $name): ?State
    {
        return State::query()
            ->get()
            ->first(fn (State $state): bool => $this->namesMatch($state->name, $name));
    }

    private function findCity(int $stateId, string $name): ?City
    {
        return City::query()
            ->where('state_id', $stateId)
            ->get()
            ->first(fn (City $city): bool => $this->namesMatch($city->name, $name));
    }

    private function namesMatch(string $left, string $right): bool
    {
        return $this->normalize($left) === $this->normalize($right);
    }

    private function normalize(string $value): string
    {
        $ascii = Str::ascii(mb_strtolower(trim($value)));

        return (string) preg_replace('/\s+/', ' ', $ascii);
    }
}
