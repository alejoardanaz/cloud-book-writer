<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (env('DB_SEED', false)) {
            $this->seed(env('SEEDER', 'DatabaseSeeder')); // Seeder por defecto
        }
    }
}
