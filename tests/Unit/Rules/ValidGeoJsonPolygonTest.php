<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidGeoJsonPolygon;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidGeoJsonPolygonTest extends TestCase
{
    public static function invalidGeometriesProvider(): array
    {
        return [
            'not array' => ['texto'],
            'wrong type' => [[
                'type' => 'Point',
                'coordinates' => [-100.3, 25.6],
            ]],
            'missing coordinates' => [[
                'type' => 'Polygon',
            ]],
            'too few vertices' => [[
                'type' => 'Polygon',
                'coordinates' => [[
                    [-100.3, 25.6],
                    [-100.2, 25.6],
                    [-100.3, 25.6],
                ]],
            ]],
            'invalid longitude' => [[
                'type' => 'Polygon',
                'coordinates' => [[
                    [-200.0, 25.6],
                    [-100.2, 25.6],
                    [-100.2, 25.7],
                    [-200.0, 25.6],
                ]],
            ]],
            'not closed' => [[
                'type' => 'Polygon',
                'coordinates' => [[
                    [-100.3, 25.6],
                    [-100.2, 25.6],
                    [-100.2, 25.7],
                    [-100.25, 25.65],
                ]],
            ]],
        ];
    }

    public function test_valid_polygon_passes(): void
    {
        $geometry = [
            'type' => 'Polygon',
            'coordinates' => [[
                [-100.3161, 25.6866],
                [-100.2702, 25.6874],
                [-100.2759, 25.6502],
                [-100.3161, 25.6866],
            ]],
        ];

        $validator = Validator::make(
            ['geometry' => $geometry],
            ['geometry' => [new ValidGeoJsonPolygon]],
        );

        $this->assertTrue($validator->passes());
    }

    #[DataProvider('invalidGeometriesProvider')]
    public function test_invalid_polygon_fails(mixed $geometry): void
    {
        $validator = Validator::make(
            ['geometry' => $geometry],
            ['geometry' => [new ValidGeoJsonPolygon]],
        );

        $this->assertTrue($validator->fails());
    }
}
