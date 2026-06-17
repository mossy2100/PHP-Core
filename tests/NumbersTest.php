<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use DomainException;
use OceanMoon\Core\Numbers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test class for Numbers utility class.
 */
#[CoversClass(Numbers::class)]
final class NumbersTest extends TestCase
{
    // region Sign tests

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
     * Test that copySign throws DomainException when num is NAN.
     */
    public function testCopySignWithNanAsNum(): void
    {
        // Test that NAN as first parameter throws DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot copy sign from or to NAN.');
        Numbers::copySign(NAN, 5);
    }

    /**
     * Test that copySign throws DomainException when sign_source is NAN.
     */
    public function testCopySignWithNanAsSignSource(): void
    {
        // Test that NAN as second parameter throws DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot copy sign from or to NAN.');
        Numbers::copySign(5, NAN);
    }

    /**
     * Test that copySign throws DomainException when both parameters are NAN.
     */
    public function testCopySignWithBothNan(): void
    {
        // Test that NAN as both parameters throws DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot copy sign from or to NAN.');
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

    // endregion

    // region equal tests

    /**
     * Test equal with two equal integers.
     */
    public function testEqualWithEqualIntegers(): void
    {
        $this->assertTrue(Numbers::equal(5, 5));
        $this->assertTrue(Numbers::equal(0, 0));
        $this->assertTrue(Numbers::equal(-42, -42));
        $this->assertTrue(Numbers::equal(1000000, 1000000));
    }

    /**
     * Test equal with two different integers.
     */
    public function testEqualWithDifferentIntegers(): void
    {
        $this->assertFalse(Numbers::equal(5, 6));
        $this->assertFalse(Numbers::equal(0, 1));
        $this->assertFalse(Numbers::equal(-42, 42));
        $this->assertFalse(Numbers::equal(1000000, 1000001));
    }

    /**
     * Test equal with two equal floats.
     */
    public function testEqualWithEqualFloats(): void
    {
        $this->assertTrue(Numbers::equal(5.0, 5.0));
        $this->assertTrue(Numbers::equal(0.0, 0.0));
        $this->assertTrue(Numbers::equal(-42.5, -42.5));
        $this->assertTrue(Numbers::equal(1.23456789, 1.23456789));
    }

    /**
     * Test equal with two different floats.
     */
    public function testEqualWithDifferentFloats(): void
    {
        $this->assertFalse(Numbers::equal(5.0, 5.1));
        $this->assertFalse(Numbers::equal(0.0, 0.1));
        $this->assertFalse(Numbers::equal(-42.5, -42.6));
        $this->assertFalse(Numbers::equal(1.0, 1.0 + PHP_FLOAT_EPSILON));
    }

    /**
     * Test equal with mixed int and float (equal values).
     */
    public function testEqualWithMixedIntFloatEqual(): void
    {
        $this->assertTrue(Numbers::equal(5, 5.0));
        $this->assertTrue(Numbers::equal(5.0, 5));
        $this->assertTrue(Numbers::equal(0, 0.0));
        $this->assertTrue(Numbers::equal(0.0, 0));
        $this->assertTrue(Numbers::equal(-42, -42.0));
        $this->assertTrue(Numbers::equal(-42.0, -42));
    }

    /**
     * Test equal with mixed int and float (different values).
     */
    public function testEqualWithMixedIntFloatDifferent(): void
    {
        $this->assertFalse(Numbers::equal(5, 5.1));
        $this->assertFalse(Numbers::equal(5.1, 5));
        $this->assertFalse(Numbers::equal(0, 0.1));
        $this->assertFalse(Numbers::equal(0.1, 0));
    }

    /**
     * Test equal with positive and negative zero.
     */
    public function testEqualWithZeros(): void
    {
        $this->assertTrue(Numbers::equal(0, 0));
        $this->assertTrue(Numbers::equal(0.0, 0.0));
        $this->assertTrue(Numbers::equal(0, 0.0));
        $this->assertTrue(Numbers::equal(0.0, 0));
        $this->assertTrue(Numbers::equal(0.0, -0.0));
        $this->assertTrue(Numbers::equal(-0.0, 0.0));
    }

    /**
     * Test equal with special float values.
     */
    public function testEqualWithSpecialFloats(): void
    {
        // INF
        $this->assertTrue(Numbers::equal(INF, INF));
        $this->assertFalse(Numbers::equal(INF, -INF));
        $this->assertFalse(Numbers::equal(INF, 1.0));

        // -INF
        $this->assertTrue(Numbers::equal(-INF, -INF));
        $this->assertFalse(Numbers::equal(-INF, INF));
        $this->assertFalse(Numbers::equal(-INF, -1.0));

        // NAN (NAN !== NAN by IEEE 754)
        $this->assertFalse(Numbers::equal(NAN, NAN));
        $this->assertFalse(Numbers::equal(NAN, 1.0));
    }

    // endregion

    // region isNumber() tests

    /**
     * Test Numbers::isNumber returns true for integers.
     */
    public function testIsNumberWithIntegers(): void
    {
        $this->assertTrue(Numbers::isNumber(0));
        $this->assertTrue(Numbers::isNumber(42));
        $this->assertTrue(Numbers::isNumber(-99));
        $this->assertTrue(Numbers::isNumber(PHP_INT_MAX));
        $this->assertTrue(Numbers::isNumber(PHP_INT_MIN));
    }

    /**
     * Test Numbers::isNumber returns true for floats.
     */
    public function testIsNumberWithFloats(): void
    {
        $this->assertTrue(Numbers::isNumber(0.0));
        $this->assertTrue(Numbers::isNumber(3.14));
        $this->assertTrue(Numbers::isNumber(-2.5));
        $this->assertTrue(Numbers::isNumber(1e10));
        $this->assertTrue(Numbers::isNumber(PHP_FLOAT_MAX));
        $this->assertTrue(Numbers::isNumber(PHP_FLOAT_MIN));
        $this->assertTrue(Numbers::isNumber(PHP_FLOAT_EPSILON));
    }

    /**
     * Test Numbers::isNumber returns true for special float values.
     */
    public function testIsNumberWithSpecialFloats(): void
    {
        $this->assertTrue(Numbers::isNumber(INF));
        $this->assertTrue(Numbers::isNumber(-INF));
        $this->assertTrue(Numbers::isNumber(NAN));
        $this->assertTrue(Numbers::isNumber(-0.0));
    }

    /**
     * Test Numbers::isNumber returns false for numeric strings.
     */
    public function testIsNumberWithNumericStrings(): void
    {
        $this->assertFalse(Numbers::isNumber('42'));
        $this->assertFalse(Numbers::isNumber('3.14'));
        $this->assertFalse(Numbers::isNumber('-99'));
        $this->assertFalse(Numbers::isNumber('1e10'));
        $this->assertFalse(Numbers::isNumber('0x1A'));
    }

    /**
     * Test Numbers::isNumber returns false for non-numeric types.
     */
    public function testIsNumberWithNonNumericTypes(): void
    {
        $this->assertFalse(Numbers::isNumber('hello'));
        $this->assertFalse(Numbers::isNumber(''));
        $this->assertFalse(Numbers::isNumber(true));
        $this->assertFalse(Numbers::isNumber(false));
        $this->assertFalse(Numbers::isNumber(null));
        $this->assertFalse(Numbers::isNumber([]));
        $this->assertFalse(Numbers::isNumber([1, 2]));
        $this->assertFalse(Numbers::isNumber(new stdClass()));
    }

    // endregion

    // region isZero() tests

    /**
     * Test isZero() with integer zero.
     */
    public function testIsZeroWithIntegerZero(): void
    {
        $this->assertTrue(Numbers::isZero(0));
    }

    /**
     * Test isZero() with positive float zero.
     */
    public function testIsZeroWithPositiveFloatZero(): void
    {
        $this->assertTrue(Numbers::isZero(0.0));
    }

    /**
     * Test isZero() with negative float zero.
     */
    public function testIsZeroWithNegativeFloatZero(): void
    {
        $this->assertTrue(Numbers::isZero(-0.0));
    }

    /**
     * Test isZero() with non-zero integers.
     */
    public function testIsZeroWithNonZeroIntegers(): void
    {
        $this->assertFalse(Numbers::isZero(1));
        $this->assertFalse(Numbers::isZero(-1));
        $this->assertFalse(Numbers::isZero(PHP_INT_MAX));
        $this->assertFalse(Numbers::isZero(PHP_INT_MIN));
    }

    /**
     * Test isZero() with non-zero floats.
     */
    public function testIsZeroWithNonZeroFloats(): void
    {
        $this->assertFalse(Numbers::isZero(0.1));
        $this->assertFalse(Numbers::isZero(-0.1));
        $this->assertFalse(Numbers::isZero(PHP_FLOAT_EPSILON));
        $this->assertFalse(Numbers::isZero(PHP_FLOAT_MIN));
    }

    /**
     * Test isZero() with special float values.
     */
    public function testIsZeroWithSpecialFloats(): void
    {
        $this->assertFalse(Numbers::isZero(INF));
        $this->assertFalse(Numbers::isZero(-INF));
        $this->assertFalse(Numbers::isZero(NAN));
    }

    // endregion
}
