<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Globals;

use PHPUnit\Framework\TestCase;

use const Mossy2100\Core\M_TAU;
use const Mossy2100\Core\RECURSION;

/**
 * Tests the stuff in globals.php.
 */
final class GlobalsTest extends TestCase
{
    #region Constants tests.

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

    #endregion
}
