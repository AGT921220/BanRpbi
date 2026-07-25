<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidGeoJsonPolygon implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('La geometría debe ser un objeto GeoJSON válido.');

            return;
        }

        if (($value['type'] ?? null) !== 'Polygon') {
            $fail('La geometría debe ser de tipo Polygon.');

            return;
        }

        $coordinates = $value['coordinates'] ?? null;

        if (! is_array($coordinates) || $coordinates === []) {
            $fail('La geometría debe incluir un anillo exterior de coordenadas.');

            return;
        }

        $ring = $coordinates[0] ?? null;

        if (! is_array($ring) || count($ring) < 4) {
            $fail('El polígono debe tener al menos tres vértices.');

            return;
        }

        foreach ($ring as $point) {
            if (! is_array($point) || count($point) < 2) {
                $fail('Cada coordenada debe ser un par [longitud, latitud].');

                return;
            }

            $longitude = $point[0];
            $latitude = $point[1];

            if (! is_numeric($longitude) || ! is_numeric($latitude)) {
                $fail('Las coordenadas del polígono deben ser numéricas.');

                return;
            }

            $longitude = (float) $longitude;
            $latitude = (float) $latitude;

            if ($longitude < -180 || $longitude > 180) {
                $fail('La longitud debe estar entre -180 y 180.');

                return;
            }

            if ($latitude < -90 || $latitude > 90) {
                $fail('La latitud debe estar entre -90 y 90.');

                return;
            }
        }

        $first = $ring[0];
        $last = $ring[array_key_last($ring)];

        if ((float) $first[0] !== (float) $last[0] || (float) $first[1] !== (float) $last[1]) {
            $fail('El anillo exterior del polígono debe estar cerrado (primer y último punto iguales).');
        }
    }
}
