<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_true_is_true()
    {
        $this->assertTrue(true);
    }

    public function test_session_config_forces_database_driver_when_cookie_is_configured(): void
    {
        $originalSessionDriver = getenv('SESSION_DRIVER');

        putenv('SESSION_DRIVER=cookie');

        /** @var array<string, mixed> $sessionConfig */
        $sessionConfig = require base_path('config/session.php');

        $this->assertSame('database', $sessionConfig['driver']);

        if ($originalSessionDriver === false) {
            putenv('SESSION_DRIVER');
        } else {
            putenv("SESSION_DRIVER={$originalSessionDriver}");
        }
    }
}
