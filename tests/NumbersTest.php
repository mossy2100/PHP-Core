<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Numbers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Test class for Numbers utility class.
 */
#[CoversClass(Numbers::class)]
final class NumbersTest extends TestCase
{
    /**
     * Test sign detection with default behavior (zero for zero).
     */
    public function testSignDefault(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, Numbers::sign(1));
        $this->assertSame(1, Numbers::sign(42));
        $this->assertSame(1, Numbers::sign(1.5));
        $this->assertSame(1, Numbers::sign(0.001));

        // Test negative numbers return -1.
        $this->assertSame(-1, Numbers::sign(-1));
        $this->assertSame(-1, Numbers::sign(-42));
        $this->assertSame(-1, Numbers::sign(-1.5));
        $this->assertSame(-1, Numbers::sign(-0.001));

        // Test zero returns 0 (default behavior).
        $this->assertSame(0, Numbers::sign(0));
        $this->assertSame(0, Numbers::sign(0.0));
        $this->assertSame(0, Numbers::sign(-0.0));

        // Test infinity values.
        $this->assertSame(1, Numbers::sign(INF));
        $this->assertSame(-1, Numbers::sign(-INF));
    }

    /**
     * Test sign detection with zeroForZero set to false.
     */
    public function testSignNoZeroForZero(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, Numbers::sign(1, false));
        $this->assertSame(1, Numbers::sign(42.5, false));

        // Test negative numbers return -1.
        $this->assertSame(-1, Numbers::sign(-1, false));
        $this->assertSame(-1, Numbers::sign(-42.5, false));

        // Test integer zero returns 1 (positive zero).
        $this->assertSame(1, Numbers::sign(0, false));

        // Test positive float zero returns 1.
        $this->assertSame(1, Numbers::sign(0.0, false));

        // Test negative float zero returns -1.
        $this->assertSame(-1, Numbers::sign(-0.0, false));

        // Test infinity values.
        $this->assertSame(1, Numbers::sign(INF, false));
        $this->assertSame(-1, Numbers::sign(-INF, false));
    }

    /**
     * Test copying sign to positive numbers.
     */
    public function testCopySignToPositive(): void
    {
        // Test copying positive sign to positive number.
        $this->assertSame(5, Numbers::copySign(5, 10));
        $this->assertSame(5.0, Numbers::copySign(5.0, 10.0));

        // Test copying negative sign to positive number.
        $this->assertSame(-5, Numbers::copySign(5, -10));
        $this->assertSame(-5.0, Numbers::copySign(5.0, -10.0));

        // Test copying sign from zero to positive number.
        $this->assertSame(5, Numbers::copySign(5, 0));
        $this->assertSame(5.0, Numbers::copySign(5.0, 0.0));
        $this->assertSame(-5, Numbers::copySign(5, -0.0));

        // Test copying sign from infinity.
        $this->assertSame(5, Numbers::copySign(5, INF));
        $this->assertSame(-5, Numbers::copySign(5, -INF));
    }

    /**
     * Test copying sign to negative numbers.
     */
    public function testCopySignToNegative(): void
    {
        // Test copying positive sign to negative number.
        $this->assertSame(5, Numbers::copySign(-5, 10));
        $this->assertSame(5.0, Numbers::copySign(-5.0, 10.0));

        // Test copying negative sign to negative number.
        $this->assertSame(-5, Numbers::copySign(-5, -10));
        $this->assertSame(-5.0, Numbers::copySign(-5.0, -10.0));

        // Test copying sign from zero to negative number.
        $this->assertSame(5, Numbers::copySign(-5, 0));
        $this->assertSame(5.0, Numbers::copySign(-5.0, 0.0));
        $this->assertSame(-5, Numbers::copySign(-5, -0.0));
    }

    /**
     * Test copying sign to and from zero.
     */
    public function testCopySignWithZero(): void
    {
        // Test copying positive sign to zero.
        $this->assertSame(0, Numbers::copySign(0, 10));
        $this->assertSame(0.0, Numbers::copySign(0.0, 10));

        // Test copying negative sign to zero.
        $this->assertSame(0, Numbers::copySign(0, -10));
        $this->assertSame(-0.0, Numbers::copySign(0.0, -10));

        // Test copying sign from positive zero.
        $this->assertSame(5, Numbers::copySign(5, 0.0));

        // Test copying sign from negative zero.
        $this->assertSame(-5, Numbers::copySign(5, -0.0));
    }

    /**
     * Test copying sign with infinity values.
     */
    public function testCopySignWithInfinity(): void
    {
        // Test copying sign to infinity.
        $this->assertSame(INF, Numbers::copySign(INF, 10));
        $this->assertSame(-INF, Numbers::copySign(INF, -10));
        $this->assertSame(INF, Numbers::copySign(-INF, 10));
        $this->assertSame(-INF, Numbers::copySign(-INF, -10));

        // Test copying sign from infinity.
        $this->assertSame(5, Numbers::copySign(5, INF));
        $this->assertSame(-5, Numbers::copySign(5, -INF));
    }

    /**
     * Test that copySign throws ValueError when num is NaN.
     */
    public function testCopySignWithNanAsNum(): void
    {
        // Test that NaN as first parameter throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("NaN is not allowed for either parameter.");
        Numbers::copySign(NAN, 5);
    }

    /**
     * Test that copySign throws ValueError when sign_source is NaN.
     */
    public function testCopySignWithNanAsSignSource(): void
    {
        // Test that NaN as second parameter throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("NaN is not allowed for either parameter.");
        Numbers::copySign(5, NAN);
    }

    /**
     * Test that copySign throws ValueError when both parameters are NaN.
     */
    public function testCopySignWithBothNan(): void
    {
        // Test that NaN as both parameters throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("NaN is not allowed for either parameter.");
        Numbers::copySign(NAN, NAN);
    }

    /**
     * Test copySign preserves the type relationship.
     */
    public function testCopySignReturnType(): void
    {
        // Test that copySign with int parameter returns int.
        $result = Numbers::copySign(5, 10);
        $this->assertIsInt($result);

        $result = Numbers::copySign(5, -10);
        $this->assertIsInt($result);

        // Test that copySign with float parameter returns float.
        $result = Numbers::copySign(5.0, 10);
        $this->assertIsFloat($result);

        $result = Numbers::copySign(5.0, -10.0);
        $this->assertIsFloat($result);
    }
}
