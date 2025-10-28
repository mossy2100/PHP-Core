<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use ValueError;

/**
 * Test class for Number utility class.
 */
#[CoversClass(Number::class)]
final class NumberTest extends TestCase
{
    /**
     * Test detection of numeric types.
     */
    public function testIsNumber(): void
    {
        // Test that integers are identified as numbers.
        $this->assertTrue(Type::isNumber(0));
        $this->assertTrue(Type::isNumber(42));
        $this->assertTrue(Type::isNumber(-17));

        // Test that floats are identified as numbers.
        $this->assertTrue(Type::isNumber(0.0));
        $this->assertTrue(Type::isNumber(3.14));
        $this->assertTrue(Type::isNumber(-2.5));

        // Test that special float values are identified as numbers.
        $this->assertTrue(Type::isNumber(INF));
        $this->assertTrue(Type::isNumber(-INF));
        $this->assertTrue(Type::isNumber(NAN));

        // Test that numeric strings are NOT identified as numbers.
        $this->assertFalse(Type::isNumber("42"));
        $this->assertFalse(Type::isNumber("3.14"));

        // Test that other types are not identified as numbers.
        $this->assertFalse(Type::isNumber("hello"));
        $this->assertFalse(Type::isNumber(true));
        $this->assertFalse(Type::isNumber(false));
        $this->assertFalse(Type::isNumber(null));
        $this->assertFalse(Type::isNumber([]));
        $this->assertFalse(Type::isNumber(new stdClass()));
    }

    /**
     * Test detection of unsigned integers.
     */
    public function testIsUint(): void
    {
        // Test that zero is identified as unsigned integer.
        $this->assertTrue(Type::isUint(0));

        // Test that positive integers are identified as unsigned integers.
        $this->assertTrue(Type::isUint(1));
        $this->assertTrue(Type::isUint(42));
        $this->assertTrue(Type::isUint(1000000));

        // Test that negative integers are NOT identified as unsigned integers.
        $this->assertFalse(Type::isUint(-1));
        $this->assertFalse(Type::isUint(-42));

        // Test that floats are NOT identified as unsigned integers.
        $this->assertFalse(Type::isUint(0.0));
        $this->assertFalse(Type::isUint(3.14));
        $this->assertFalse(Type::isUint(-2.5));

        // Test that other types are not identified as unsigned integers.
        $this->assertFalse(Type::isUint("42"));
        $this->assertFalse(Type::isUint(true));
        $this->assertFalse(Type::isUint(null));
    }

    /**
     * Test sign detection with default behavior (zero for zero).
     */
    public function testSignDefault(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, Number::sign(1));
        $this->assertSame(1, Number::sign(42));
        $this->assertSame(1, Number::sign(1.5));
        $this->assertSame(1, Number::sign(0.001));

        // Test negative numbers return -1.
        $this->assertSame(-1, Number::sign(-1));
        $this->assertSame(-1, Number::sign(-42));
        $this->assertSame(-1, Number::sign(-1.5));
        $this->assertSame(-1, Number::sign(-0.001));

        // Test zero returns 0 (default behavior).
        $this->assertSame(0, Number::sign(0));
        $this->assertSame(0, Number::sign(0.0));
        $this->assertSame(0, Number::sign(-0.0));

        // Test infinity values.
        $this->assertSame(1, Number::sign(INF));
        $this->assertSame(-1, Number::sign(-INF));
    }

    /**
     * Test sign detection with zeroForZero set to false.
     */
    public function testSignNoZeroForZero(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, Number::sign(1, false));
        $this->assertSame(1, Number::sign(42.5, false));

        // Test negative numbers return -1.
        $this->assertSame(-1, Number::sign(-1, false));
        $this->assertSame(-1, Number::sign(-42.5, false));

        // Test integer zero returns 1 (positive zero).
        $this->assertSame(1, Number::sign(0, false));

        // Test positive float zero returns 1.
        $this->assertSame(1, Number::sign(0.0, false));

        // Test negative float zero returns -1.
        $this->assertSame(-1, Number::sign(-0.0, false));

        // Test infinity values.
        $this->assertSame(1, Number::sign(INF, false));
        $this->assertSame(-1, Number::sign(-INF, false));
    }

    /**
     * Test copying sign to positive numbers.
     */
    public function testCopySignToPositive(): void
    {
        // Test copying positive sign to positive number.
        $this->assertSame(5, Number::copySign(5, 10));
        $this->assertSame(5.0, Number::copySign(5.0, 10.0));

        // Test copying negative sign to positive number.
        $this->assertSame(-5, Number::copySign(5, -10));
        $this->assertSame(-5.0, Number::copySign(5.0, -10.0));

        // Test copying sign from zero to positive number.
        $this->assertSame(5, Number::copySign(5, 0));
        $this->assertSame(5.0, Number::copySign(5.0, 0.0));
        $this->assertSame(-5, Number::copySign(5, -0.0));

        // Test copying sign from infinity.
        $this->assertSame(5, Number::copySign(5, INF));
        $this->assertSame(-5, Number::copySign(5, -INF));
    }

    /**
     * Test copying sign to negative numbers.
     */
    public function testCopySignToNegative(): void
    {
        // Test copying positive sign to negative number.
        $this->assertSame(5, Number::copySign(-5, 10));
        $this->assertSame(5.0, Number::copySign(-5.0, 10.0));

        // Test copying negative sign to negative number.
        $this->assertSame(-5, Number::copySign(-5, -10));
        $this->assertSame(-5.0, Number::copySign(-5.0, -10.0));

        // Test copying sign from zero to negative number.
        $this->assertSame(5, Number::copySign(-5, 0));
        $this->assertSame(5.0, Number::copySign(-5.0, 0.0));
        $this->assertSame(-5, Number::copySign(-5, -0.0));
    }

    /**
     * Test copying sign to and from zero.
     */
    public function testCopySignWithZero(): void
    {
        // Test copying positive sign to zero.
        $this->assertSame(0, Number::copySign(0, 10));
        $this->assertSame(0.0, Number::copySign(0.0, 10));

        // Test copying negative sign to zero.
        $this->assertSame(0, Number::copySign(0, -10));
        $this->assertSame(-0.0, Number::copySign(0.0, -10));

        // Test copying sign from positive zero.
        $this->assertSame(5, Number::copySign(5, 0.0));

        // Test copying sign from negative zero.
        $this->assertSame(-5, Number::copySign(5, -0.0));
    }

    /**
     * Test copying sign with infinity values.
     */
    public function testCopySignWithInfinity(): void
    {
        // Test copying sign to infinity.
        $this->assertSame(INF, Number::copySign(INF, 10));
        $this->assertSame(-INF, Number::copySign(INF, -10));
        $this->assertSame(INF, Number::copySign(-INF, 10));
        $this->assertSame(-INF, Number::copySign(-INF, -10));

        // Test copying sign from infinity.
        $this->assertSame(5, Number::copySign(5, INF));
        $this->assertSame(-5, Number::copySign(5, -INF));
    }

    /**
     * Test that copySign throws ValueError when num is NaN.
     */
    public function testCopySignWithNanAsNum(): void
    {
        // Test that NaN as first parameter throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("NaN is not allowed for either parameter.");
        Number::copySign(NAN, 5);
    }

    /**
     * Test that copySign throws ValueError when sign_source is NaN.
     */
    public function testCopySignWithNanAsSignSource(): void
    {
        // Test that NaN as second parameter throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("NaN is not allowed for either parameter.");
        Number::copySign(5, NAN);
    }

    /**
     * Test that copySign throws ValueError when both parameters are NaN.
     */
    public function testCopySignWithBothNan(): void
    {
        // Test that NaN as both parameters throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("NaN is not allowed for either parameter.");
        Number::copySign(NAN, NAN);
    }

    /**
     * Test copySign preserves the type relationship.
     */
    public function testCopySignReturnType(): void
    {
        // Test that copySign with int parameter returns int.
        $result = Number::copySign(5, 10);
        $this->assertIsInt($result);

        $result = Number::copySign(5, -10);
        $this->assertIsInt($result);

        // Test that copySign with float parameter returns float.
        $result = Number::copySign(5.0, 10);
        $this->assertIsFloat($result);

        $result = Number::copySign(5.0, -10.0);
        $this->assertIsFloat($result);
    }
}
