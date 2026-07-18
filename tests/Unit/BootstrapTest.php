<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function test_php_runtime_is_available(): void
    {
        $version = phpversion();

        $this->assertIsString($version);
        $this->assertNotSame('', $version);
    }
}
