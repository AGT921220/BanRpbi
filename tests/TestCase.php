<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            $this->fail('Hay configuración cacheada. Ejecuta php artisan config:clear antes de los tests para no usar la base real.');
        }

        parent::setUp();
    }
}
