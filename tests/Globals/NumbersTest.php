<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Globals;

use DomainException;
use PHPUnit\Framework\TestCase;
use stdClass;

use function OceanMoon\Core\Globals\copy_sign;
use function OceanMoon\Core\Globals\is_number;
use function OceanMoon\Core\Globals\is_zero;
use function OceanMoon\Core\Globals\sign;

/**
 * Test class for Numbers utility class.
 */
final class NumbersTest extends TestCase
{
    #region is_number() tests

    /**
     * Test is_number returns true for integers.
     */
    public function testIsNumberWithIntegers(): void
    {
        $this->assertTrue(is_number(0)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(42)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(-99)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(PHP_INT_MAX)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(PHP_INT_MIN)); // @phpstan-ignore function.alreadyNarrowedType
    }

    /**
     * Test is_number returns true for floats.
     */
    public function testIsNumberWithFloats(): void
    {
        $this->assertTrue(is_number(0.0)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(3.14)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(-2.5)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(1e10)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(PHP_FLOAT_MAX)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(PHP_FLOAT_MIN)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(PHP_FLOAT_EPSILON)); // @phpstan-ignore function.alreadyNarrowedType
    }

    /**
     * Test is_number returns true for special float values.
     */
    public function testIsNumberWithSpecialFloats(): void
    {
        $this->assertTrue(is_number(INF)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(-INF)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(NAN)); // @phpstan-ignore function.alreadyNarrowedType
        $this->assertTrue(is_number(-0.0)); // @phpstan-ignore function.alreadyNarrowedType
    }

    /**
     * Test is_number returns false for numeric strings.
     */
    public function testIsNumberWithNumericStrings(): void
    {
        $this->assertFalse(is_number('42')); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number('3.14')); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number('-99')); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number('1e10')); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number('0x1A')); // @phpstan-ignore function.impossibleType
    }

    /**
     * Test is_number returns false for non-numeric types.
     */
    public function testIsNumberWithNonNumericTypes(): void
    {
        $this->assertFalse(is_number('hello')); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number('')); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number(true)); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number(false)); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number(null)); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number([])); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number([1, 2])); // @phpstan-ignore function.impossibleType
        $this->assertFalse(is_number(new stdClass())); // @phpstan-ignore function.impossibleType
    }

    #endregion

    #region is_zero() tests

    /**
     * Test is_zero() with integer zero.
     */
    public function testIsZeroWithIntegerZero(): void
    {
        $this->assertTrue(is_zero(0));
    }

    /**
     * Test is_zero() with positive float zero.
     */
    public function testIsZeroWithPositiveFloatZero(): void
    {
        $this->assertTrue(is_zero(0.0));
    }

    /**
     * Test is_zero() with negative float zero.
     */
    public function testIsZeroWithNegativeFloatZero(): void
    {
        $this->assertTrue(is_zero(-0.0));
    }

    /**
     * Test is_zero() with non-zero integers.
     */
    public function testIsZeroWithNonZeroIntegers(): void
    {
        $this->assertFalse(is_zero(1));
        $this->assertFalse(is_zero(-1));
        $this->assertFalse(is_zero(PHP_INT_MAX));
        $this->assertFalse(is_zero(PHP_INT_MIN));
    }

    /**
     * Test is_zero() with non-zero floats.
     */
    public function testIsZeroWithNonZeroFloats(): void
    {
        $this->assertFalse(is_zero(0.1));
        $this->assertFalse(is_zero(-0.1));
        $this->assertFalse(is_zero(PHP_FLOAT_EPSILON));
        $this->assertFalse(is_zero(PHP_FLOAT_MIN));
    }

    /**
     * Test is_zero() with special float values.
     */
    public function testIsZeroWithSpecialFloats(): void
    {
        $this->assertFalse(is_zero(INF));
        $this->assertFalse(is_zero(-INF));
        $this->assertFalse(is_zero(NAN));
    }

    #endregion

    #region sign() tests

    /**
     * Test sign detection with default behavior (zero for zero).
     */
    public function testSignDefault(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, sign(1));
        $this->assertSame(1, sign(42));
        $this->assertSame(1, sign(1.5));
        $this->assertSame(1, sign(0.001));

        // Test negative numbers return -1.
        $this->assertSame(-1, sign(-1));
        $this->assertSame(-1, sign(-42));
        $this->assertSame(-1, sign(-1.5));
        $this->assertSame(-1, sign(-0.001));

        // Test zero returns 0 (default behavior).
        $this->assertSame(0, sign(0));
        $this->assertSame(0, sign(0.0));
        $this->assertSame(0, sign(-0.0));

        // Test infinity values.
        $this->assertSame(1, sign(INF));
        $this->assertSame(-1, sign(-INF));
    }

    /**
     * Test sign detection with zeroForZero set to false.
     */
    public function testSignNoZeroForZero(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, sign(1, false));
        $this->assertSame(1, sign(42.5, false));

        // Test negative numbers return -1.
        $this->assertSame(-1, sign(-1, false));
        $this->assertSame(-1, sign(-42.5, false));

        // Test integer zero returns 1 (positive zero).
        $this->assertSame(1, sign(0, false));

        // Test positive float zero returns 1.
        $this->assertSame(1, sign(0.0, false));

        // Test negative float zero returns -1.
        $this->assertSame(-1, sign(-0.0, false));

        // Test infinity values.
        $this->assertSame(1, sign(INF, false));
        $this->assertSame(-1, sign(-INF, false));
    }

    #endregion

    #region copy_sign() tests

    /**
     * Test copying sign to positive numbers.
     */
    public function testCopySignToPositive(): void
    {
        // Test copying positive sign to positive number.
        $this->assertSame(5, copy_sign(5, 10));
        $this->assertSame(5.0, copy_sign(5.0, 10.0));

        // Test copying negative sign to positive number.
        $this->assertSame(-5, copy_sign(5, -10));
        $this->assertSame(-5.0, copy_sign(5.0, -10.0));

        // Test copying sign from zero to positive number.
        $this->assertSame(5, copy_sign(5, 0));
        $this->assertSame(5.0, copy_sign(5.0, 0.0));
        $this->assertSame(-5, copy_sign(5, -0.0));

        // Test copying sign from infinity.
        $this->assertSame(5, copy_sign(5, INF));
        $this->assertSame(-5, copy_sign(5, -INF));
    }

    /**
     * Test copying sign to negative numbers.
     */
    public function testCopySignToNegative(): void
    {
        // Test copying positive sign to negative number.
        $this->assertSame(5, copy_sign(-5, 10));
        $this->assertSame(5.0, copy_sign(-5.0, 10.0));

        // Test copying negative sign to negative number.
        $this->assertSame(-5, copy_sign(-5, -10));
        $this->assertSame(-5.0, copy_sign(-5.0, -10.0));

        // Test copying sign from zero to negative number.
        $this->assertSame(5, copy_sign(-5, 0));
        $this->assertSame(5.0, copy_sign(-5.0, 0.0));
        $this->assertSame(-5, copy_sign(-5, -0.0));
    }

    /**
     * Test copying sign to and from zero.
     */
    public function testCopySignWithZero(): void
    {
        // Test copying positive sign to zero.
        $this->assertSame(0, copy_sign(0, 10));
        $this->assertSame(0.0, copy_sign(0.0, 10));

        // Test copying negative sign to zero.
        $this->assertSame(0, copy_sign(0, -10));
        $this->assertSame(-0.0, copy_sign(0.0, -10));

        // Test copying sign from positive zero.
        $this->assertSame(5, copy_sign(5, 0.0));

        // Test copying sign from negative zero.
        $this->assertSame(-5, copy_sign(5, -0.0));
    }

    /**
     * Test copying sign with infinity values.
     */
    public function testCopySignWithInfinity(): void
    {
        // Test copying sign to infinity.
        $this->assertSame(INF, copy_sign(INF, 10));
        $this->assertSame(-INF, copy_sign(INF, -10));
        $this->assertSame(INF, copy_sign(-INF, 10));
        $this->assertSame(-INF, copy_sign(-INF, -10));

        // Test copying sign from infinity.
        $this->assertSame(5, copy_sign(5, INF));
        $this->assertSame(-5, copy_sign(5, -INF));
    }

    /**
     * Test that copy_sign throws DomainException when num is NAN.
     */
    public function testCopySignWithNanAsNum(): void
    {
        // Test that NAN as first parameter throws DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot copy sign to or from NAN.');
        copy_sign(NAN, 5);
    }

    /**
     * Test that copy_sign throws DomainException when sign_source is NAN.
     */
    public function testCopySignWithNanAsSignSource(): void
    {
        // Test that NAN as second parameter throws DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot copy sign to or from NAN.');
        copy_sign(5, NAN);
    }

    /**
     * Test that copy_sign throws DomainException when both parameters are NAN.
     */
    public function testCopySignWithBothNan(): void
    {
        // Test that NAN as both parameters throws DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot copy sign to or from NAN.');
        copy_sign(NAN, NAN);
    }

    /**
     * Test copy_sign preserves the type relationship.
     */
    public function testCopySignReturnType(): void
    {
        // Test that copy_sign with int parameter returns int.
        $result = copy_sign(5, 10);
        $this->assertIsInt($result);

        $result = copy_sign(5, -10);
        $this->assertIsInt($result);

        // Test that copy_sign with float parameter returns float.
        $result = copy_sign(5.0, 10);
        $this->assertIsFloat($result);

        $result = copy_sign(5.0, -10.0);
        $this->assertIsFloat($result);
    }

    #endregion
}
