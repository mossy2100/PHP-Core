<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Globals;

use PHPUnit\Framework\TestCase;

use const OceanMoon\Core\M_TAU;
use const OceanMoon\Core\RECURSION;

/**
 * Tests the constants in constants.php.
 */
final class ConstantsTest extends TestCase
{
    /**
     * Test M_TAU equals 2π.
     */
    public function testMTau(): void
    {
        $this->assertSame(2 * M_PI, M_TAU);
    }

    /**
     * Test RECURSION matches PHP's own print_r() recursion marker text.
     */
    public function testRecursion(): void
    {
        $this->assertSame('*RECURSION*', RECURSION);
    }
}
