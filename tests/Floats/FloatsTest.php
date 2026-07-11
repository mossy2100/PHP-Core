<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Floats;

use DomainException;
use OceanMoon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class - core comparison, transformation, precision, and inspection methods.
 */
#[CoversClass(Floats::class)]
final class FloatsTest extends TestCase
{
    #region approxEqual tests

    /**
     * Test approxEqual uses both relative and absolute tolerance.
     */
    public function testApproxEqualWithBothTolerances(): void
    {
        // Large values: relative tolerance handles scale
        $large = 1e20;
        $this->assertTrue(Floats::approxEqual($large, $large + 1e9));

        // Small values near zero: absolute tolerance handles them
        $this->assertTrue(Floats::approxEqual(0.0, PHP_FLOAT_EPSILON / 2));
        $this->assertFalse(Floats::approxEqual(0.0, PHP_FLOAT_EPSILON * 2));
    }

    /**
     * Test approxEqual with custom tolerances.
     */
    public function testApproxEqualWithCustomTolerances(): void
    {
        // 10% relative tolerance, 1.0 absolute tolerance
        $this->assertTrue(Floats::approxEqual(100.0, 105.0, 0.1, 1.0));
        $this->assertFalse(Floats::approxEqual(100.0, 115.0, 0.1, 1.0));

        // Absolute tolerance catches values near zero
        $this->assertTrue(Floats::approxEqual(0.0, 0.5, 1e-9, 1.0));
    }

    /**
     * Test approxEqual with negative tolerances throws DomainException.
     */
    public function testApproxEqualWithNegativeTolerancesThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot use negative tolerances');
        Floats::approxEqual(1.0, 1.0, -0.1, 0.0);
    }

    /**
     * Test approxEqual with zero values.
     */
    public function testApproxEqualWithZeros(): void
    {
        $this->assertTrue(Floats::approxEqual(0.0, 0.0));
        $this->assertTrue(Floats::approxEqual(0.0, -0.0));
        $this->assertTrue(Floats::approxEqual(-0.0, 0.0));
        $this->assertTrue(Floats::approxEqual(-0.0, -0.0));
    }

    /**
     * Test approxEqual with same infinities returns true.
     */
    public function testApproxEqualWithSameInfinity(): void
    {
        // INF and -INF are only considered close to themselves (matching Python's isclose behavior)
        $this->assertTrue(Floats::approxEqual(INF, INF));
        $this->assertTrue(Floats::approxEqual(-INF, -INF));
    }

    /**
     * Test approxEqual with infinity and finite value returns false.
     */
    public function testApproxEqualWithInfinityAndFinite(): void
    {
        // Infinity with finite value returns false
        $this->assertFalse(Floats::approxEqual(INF, 1.0));
        $this->assertFalse(Floats::approxEqual(1.0, INF));
        $this->assertFalse(Floats::approxEqual(-INF, 1.0));
        $this->assertFalse(Floats::approxEqual(1.0, -INF));
    }

    /**
     * Test approxEqual with opposite infinities returns false.
     */
    public function testApproxEqualWithOppositeInfinities(): void
    {
        // Opposite infinities are not close to each other
        $this->assertFalse(Floats::approxEqual(INF, -INF));
        $this->assertFalse(Floats::approxEqual(-INF, INF));
    }

    /**
     * Test approxEqual with NAN returns false.
     */
    public function testApproxEqualWithNan(): void
    {
        // NAN is never equal to anything, including itself
        $this->assertFalse(Floats::approxEqual(NAN, NAN));
    }

    /**
     * Test approxEqual with NAN and finite value returns false.
     */
    public function testApproxEqualWithNanAndFinite(): void
    {
        // NAN with any finite value returns false
        $this->assertFalse(Floats::approxEqual(NAN, 0.0));
        $this->assertFalse(Floats::approxEqual(0.0, NAN));
    }

    #endregion

    #region compare tests

    /**
     * Test compare with equal values.
     */
    public function testApproxCompareWithEqualValues(): void
    {
        $this->assertSame(0, Floats::approxCompare(1.0, 1.0));
        $this->assertSame(0, Floats::approxCompare(0.0, 0.0));
        $this->assertSame(0, Floats::approxCompare(-5.5, -5.5));
    }

    /**
     * Test compare with approximately equal values.
     */
    public function testApproxCompareWithApproximatelyEqual(): void
    {
        // Uses combined relative and absolute tolerance
        $large = 1e20;
        $this->assertSame(0, Floats::approxCompare($large, $large + 1e9));

        // Absolute tolerance handles values near zero
        $this->assertSame(0, Floats::approxCompare(0.0, PHP_FLOAT_EPSILON / 2));
    }

    /**
     * Test compare with less than.
     */
    public function testApproxCompareWithLessThan(): void
    {
        $this->assertSame(-1, Floats::approxCompare(1.0, 2.0));
        $this->assertSame(-1, Floats::approxCompare(-5.0, -4.0));
        $this->assertSame(-1, Floats::approxCompare(0.0, 1.0));
    }

    /**
     * Test compare with greater than.
     */
    public function testApproxCompareWithGreaterThan(): void
    {
        $this->assertSame(1, Floats::approxCompare(2.0, 1.0));
        $this->assertSame(1, Floats::approxCompare(-4.0, -5.0));
        $this->assertSame(1, Floats::approxCompare(1.0, 0.0));
    }

    /**
     * Test compare with custom tolerances.
     */
    public function testApproxCompareWithCustomTolerances(): void
    {
        // 10% relative, 1.0 absolute
        $this->assertSame(0, Floats::approxCompare(100.0, 105.0, 0.1, 1.0));
        $this->assertSame(-1, Floats::approxCompare(100.0, 115.0, 0.1, 1.0));
    }

    /**
     * Test compare with negative tolerance throws DomainException.
     */
    public function testApproxCompareWithNegativeToleranceThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot use negative tolerances');
        Floats::approxCompare(1.0, 1.0, -0.1, 0.0);
    }

    /**
     * Test compare with NAN throws DomainException.
     */
    public function testApproxCompareWithNanThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot compare NAN');
        Floats::approxCompare(NAN, 1.0);
    }

    /**
     * Test compare with NAN as second argument throws DomainException.
     */
    public function testApproxCompareWithNanSecondArgThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot compare NAN');
        Floats::approxCompare(1.0, NAN);
    }

    #endregion

    #region Transformation method tests

    /**
     * Test normalization of zero values.
     */
    public function testNormalizeZero(): void
    {
        // Test that negative zero is normalized to positive zero.
        $this->assertSame(0.0, Floats::normalizeZero(-0.0));

        // Test that positive zero remains positive zero.
        $this->assertSame(0.0, Floats::normalizeZero(0.0));

        // Test that positive values are unchanged.
        $this->assertSame(1.5, Floats::normalizeZero(1.5));

        // Test that negative values are unchanged.
        $this->assertSame(-2.5, Floats::normalizeZero(-2.5));

        // Test that infinity values are unchanged.
        $this->assertSame(INF, Floats::normalizeZero(INF));
        $this->assertSame(-INF, Floats::normalizeZero(-INF));

        // Test that NAN is unchanged (NAN !== NAN, so use is_nan).
        $this->assertTrue(is_nan(Floats::normalizeZero(NAN)));
    }

    /**
     * Test trunc() with positive values.
     */
    public function testTruncPositive(): void
    {
        $this->assertSame(3.0, Floats::trunc(3.7));
        $this->assertSame(3.0, Floats::trunc(3.2));
        $this->assertSame(3.0, Floats::trunc(3.0));
        $this->assertSame(0.0, Floats::trunc(0.9));
        $this->assertSame(100.0, Floats::trunc(100.999));
    }

    /**
     * Test trunc() with negative values.
     */
    public function testTruncNegative(): void
    {
        $this->assertSame(-3.0, Floats::trunc(-3.7));
        $this->assertSame(-3.0, Floats::trunc(-3.2));
        $this->assertSame(-3.0, Floats::trunc(-3.0));
        $this->assertSame(0.0, Floats::trunc(-0.9));
        $this->assertSame(-100.0, Floats::trunc(-100.999));
    }

    /**
     * Test trunc() with zero values.
     */
    public function testTruncZero(): void
    {
        $this->assertSame(0.0, Floats::trunc(0.0));
        $this->assertSame(0.0, Floats::trunc(-0.0));
        $this->assertSame(0.0, Floats::trunc(0.5));
        $this->assertSame(0.0, Floats::trunc(-0.5));
    }

    /**
     * Test trunc() with non-finite values.
     */
    public function testTruncNonFinite(): void
    {
        $this->assertSame(INF, Floats::trunc(INF));
        $this->assertSame(-INF, Floats::trunc(-INF));
        $this->assertTrue(is_nan(Floats::trunc(NAN)));
    }

    /**
     * Test trunc() differs from floor() for negative values.
     */
    public function testTruncDiffersFromFloor(): void
    {
        // floor() rounds toward -INF, trunc() rounds toward zero
        $this->assertSame(-3.0, Floats::trunc(-3.7));
        $this->assertSame(-4.0, floor(-3.7));

        $this->assertSame(-3.0, Floats::trunc(-3.2));
        $this->assertSame(-4.0, floor(-3.2));
    }

    /**
     * Test frac() with positive values.
     */
    public function testFracPositive(): void
    {
        $this->assertEqualsWithDelta(0.7, Floats::frac(3.7), 1e-10);
        $this->assertEqualsWithDelta(0.2, Floats::frac(3.2), 1e-10);
        $this->assertEqualsWithDelta(0.0, Floats::frac(3.0), 1e-10);
        $this->assertEqualsWithDelta(0.9, Floats::frac(0.9), 1e-10);
        $this->assertEqualsWithDelta(0.999, Floats::frac(100.999), 1e-10);
    }

    /**
     * Test frac() with negative values.
     */
    public function testFracNegative(): void
    {
        $this->assertEqualsWithDelta(-0.7, Floats::frac(-3.7), 1e-10);
        $this->assertEqualsWithDelta(-0.2, Floats::frac(-3.2), 1e-10);
        $this->assertEqualsWithDelta(0.0, Floats::frac(-3.0), 1e-10);
        $this->assertEqualsWithDelta(-0.9, Floats::frac(-0.9), 1e-10);
        $this->assertEqualsWithDelta(-0.999, Floats::frac(-100.999), 1e-10);
    }

    /**
     * Test frac() with zero values.
     */
    public function testFracZero(): void
    {
        $this->assertSame(0.0, Floats::frac(0.0));
        $this->assertSame(0.0, Floats::frac(-0.0));
    }

    /**
     * Test frac() with non-finite values.
     */
    public function testFracNonFinite(): void
    {
        // Infinity has no fractional part.
        $this->assertSame(0.0, Floats::frac(INF));
        $this->assertSame(0.0, Floats::frac(-INF));
        // NAN is still NAN.
        $this->assertTrue(is_nan(Floats::frac(NAN)));
    }

    /**
     * Test frac() satisfies the identity x = trunc(x) + frac(x).
     */
    public function testFracIdentity(): void
    {
        $testValues = [3.7, -3.7, 0.5, -0.5, 100.999, -100.999, 0.0, 42.0, -42.0];

        foreach ($testValues as $value) {
            $this->assertEqualsWithDelta(
                $value,
                Floats::trunc($value) + Floats::frac($value),
                1e-10,
                "Identity x = trunc(x) + frac(x) failed for x = $value"
            );
        }
    }

    /**
     * Test wrap() with values already within the signed range.
     */
    public function testWrapSignedValuesInRange(): void
    {
        // Values already in (-180, 180] should remain unchanged.
        $this->assertSame(0.0, Floats::wrap(0.0, 360.0));
        $this->assertSame(45.0, Floats::wrap(45.0, 360.0));
        $this->assertSame(-45.0, Floats::wrap(-45.0, 360.0));
        $this->assertSame(179.0, Floats::wrap(179.0, 360.0));
        $this->assertSame(-179.0, Floats::wrap(-179.0, 360.0));
    }

    /**
     * Test wrap() with values already within the unsigned range.
     */
    public function testWrapUnsignedValuesInRange(): void
    {
        // Values already in [0, 360) should remain unchanged.
        $this->assertSame(0.0, Floats::wrap(0.0, 360.0, signed: false));
        $this->assertSame(45.0, Floats::wrap(45.0, 360.0, signed: false));
        $this->assertSame(270.0, Floats::wrap(270.0, 360.0, signed: false));
        $this->assertSame(359.0, Floats::wrap(359.0, 360.0, signed: false));
    }

    /**
     * Test wrap() signed boundary conditions.
     * Signed range is (-180, 180] so -180 is excluded and 180 is included.
     */
    public function testWrapSignedBoundaries(): void
    {
        // 180 is included in the signed range.
        $this->assertSame(180.0, Floats::wrap(180.0, 360.0));

        // -180 is excluded, should wrap to 180.
        $this->assertSame(180.0, Floats::wrap(-180.0, 360.0));

        // Values just inside the boundaries.
        $this->assertSame(179.9, Floats::wrap(179.9, 360.0));
        $this->assertSame(-179.9, Floats::wrap(-179.9, 360.0));
    }

    /**
     * Test wrap() unsigned boundary conditions.
     * Unsigned range is [0, 360) so 0 is included and 360 is excluded.
     */
    public function testWrapUnsignedBoundaries(): void
    {
        // 0 is included in the unsigned range.
        $this->assertSame(0.0, Floats::wrap(0.0, 360.0, signed: false));

        // 360 is excluded, should wrap to 0.
        $this->assertSame(0.0, Floats::wrap(360.0, 360.0, signed: false));

        // Values just inside the boundaries.
        $this->assertSame(0.1, Floats::wrap(0.1, 360.0, signed: false));
        $this->assertSame(359.9, Floats::wrap(359.9, 360.0, signed: false));
    }

    /**
     * Test wrap() with values requiring positive wrapping (signed).
     */
    public function testWrapSignedPositiveValues(): void
    {
        // Values > 180 should wrap into the negative range.
        $this->assertSame(-90.0, Floats::wrap(270.0, 360.0));
        $this->assertSame(0.0, Floats::wrap(360.0, 360.0));
        $this->assertSame(90.0, Floats::wrap(450.0, 360.0));
        $this->assertSame(180.0, Floats::wrap(540.0, 360.0));

        // Multiple full rotations.
        $this->assertSame(90.0, Floats::wrap(810.0, 360.0)); // 2*360 + 90
    }

    /**
     * Test wrap() with values requiring negative wrapping (signed).
     */
    public function testWrapSignedNegativeValues(): void
    {
        // Values < -180 should wrap into the positive range.
        $this->assertSame(90.0, Floats::wrap(-270.0, 360.0));
        $this->assertSame(0.0, Floats::wrap(-360.0, 360.0));
        $this->assertSame(-90.0, Floats::wrap(-450.0, 360.0));
        $this->assertSame(180.0, Floats::wrap(-540.0, 360.0));

        // Multiple full rotations.
        $this->assertSame(-90.0, Floats::wrap(-810.0, 360.0)); // -2*360 - 90
    }

    /**
     * Test wrap() with values requiring wrapping (unsigned).
     */
    public function testWrapUnsignedWrapping(): void
    {
        // Positive values >= 360.
        $this->assertSame(0.0, Floats::wrap(360.0, 360.0, signed: false));
        $this->assertSame(90.0, Floats::wrap(450.0, 360.0, signed: false));
        $this->assertSame(0.0, Floats::wrap(720.0, 360.0, signed: false));

        // Negative values should wrap to positive range.
        $this->assertSame(270.0, Floats::wrap(-90.0, 360.0, signed: false));
        $this->assertSame(180.0, Floats::wrap(-180.0, 360.0, signed: false));
        $this->assertSame(0.0, Floats::wrap(-360.0, 360.0, signed: false));
        $this->assertSame(270.0, Floats::wrap(-450.0, 360.0, signed: false));
    }

    /**
     * Test wrap() with radians (default unitsPerTurn).
     */
    public function testWrapRadians(): void
    {
        // Signed (default): range is (-π, π].
        $this->assertSame(0.0, Floats::wrap(0.0));
        $this->assertSame(M_PI, Floats::wrap(M_PI));
        $this->assertSame(M_PI, Floats::wrap(-M_PI));
        $this->assertEqualsWithDelta(0.0, Floats::wrap(Floats::TAU), 1e-10);
        $this->assertEqualsWithDelta(-M_PI / 2, Floats::wrap(3 * M_PI / 2), 1e-10);

        // Unsigned: range is [0, τ).
        $this->assertSame(0.0, Floats::wrap(0.0, signed: false));
        $this->assertSame(M_PI, Floats::wrap(M_PI, signed: false));
        $this->assertSame(M_PI, Floats::wrap(-M_PI, signed: false));
        $this->assertEqualsWithDelta(0.0, Floats::wrap(Floats::TAU, signed: false), 1e-10);
        $this->assertEqualsWithDelta(3 * M_PI / 2, Floats::wrap(-M_PI / 2, signed: false), 1e-10);
    }

    /**
     * Test wrap() with other unit systems.
     */
    public function testWrapOtherUnits(): void
    {
        // Gradians (400 per turn).
        $this->assertSame(0.0, Floats::wrap(0.0, 400.0));
        $this->assertSame(100.0, Floats::wrap(100.0, 400.0));
        $this->assertSame(-100.0, Floats::wrap(300.0, 400.0));
        $this->assertSame(0.0, Floats::wrap(400.0, 400.0));

        // Turns (1 per turn).
        $this->assertSame(0.0, Floats::wrap(0.0, 1.0));
        $this->assertSame(0.25, Floats::wrap(0.25, 1.0));
        $this->assertSame(-0.25, Floats::wrap(0.75, 1.0));
        $this->assertSame(0.0, Floats::wrap(1.0, 1.0));

        // Hours (24-hour clock, unsigned).
        $this->assertSame(0.0, Floats::wrap(0.0, 24.0, signed: false));
        $this->assertSame(6.0, Floats::wrap(6.0, 24.0, signed: false));
        $this->assertSame(18.0, Floats::wrap(18.0, 24.0, signed: false));
        $this->assertSame(1.0, Floats::wrap(25.0, 24.0, signed: false));
        $this->assertSame(21.0, Floats::wrap(-3.0, 24.0, signed: false));
    }

    /**
     * Test conversion of floats to hexadecimal strings.
     */
    public function testToHex(): void
    {
        // Test that positive zero produces a consistent hex string.
        $hexZero = Floats::toHex(0.0);
        $this->assertSame(16, strlen($hexZero));

        // Test that negative zero produces a different hex string than positive zero.
        $hexNegZero = Floats::toHex(-0.0);
        $this->assertSame(16, strlen($hexNegZero));
        $this->assertNotSame($hexZero, $hexNegZero);

        // Test that a regular value produces a 16-character hex string.
        $hex1 = Floats::toHex(1.0);
        $this->assertSame(16, strlen($hex1));

        // Test that different values produce different hex strings.
        $hex2 = Floats::toHex(2.0);
        $this->assertNotSame($hex1, $hex2);

        // Test that special values produce valid hex strings.
        $this->assertSame(16, strlen(Floats::toHex(INF)));
        $this->assertSame(16, strlen(Floats::toHex(-INF)));
        $this->assertSame(16, strlen(Floats::toHex(NAN)));

        // Test that very close but different values produce different hex strings.
        $this->assertNotSame(Floats::toHex(1.0), Floats::toHex(1.0 + PHP_FLOAT_EPSILON));
    }

    /**
     * Test toHex with specific expected hex values for special floats.
     */
    public function testToHexSpecialValues(): void
    {
        // Positive zero: all bits are 0.
        $this->assertSame('0000000000000000', Floats::toHex(0.0));

        // Negative zero: sign bit is 1, all other bits are 0.
        $this->assertSame('8000000000000000', Floats::toHex(-0.0));

        // Positive infinity: sign=0, exponent=2047 (all 1s), fraction=0.
        $this->assertSame('7ff0000000000000', Floats::toHex(INF));

        // Negative infinity: sign=1, exponent=2047 (all 1s), fraction=0.
        $this->assertSame('fff0000000000000', Floats::toHex(-INF));

        // NAN: PHP's canonical NAN representation.
        $this->assertSame('7ff8000000000000', Floats::toHex(NAN));
    }

    /**
     * Test tryConvertToInt with floats that equal whole numbers.
     */
    public function testTryConvertToIntWithWholeNumbers(): void
    {
        $this->assertSame(5, Floats::tryConvertToInt(5.0));
        $this->assertSame(-10, Floats::tryConvertToInt(-10.0));
        $this->assertSame(0, Floats::tryConvertToInt(0.0));
        $this->assertSame(1000000, Floats::tryConvertToInt(1000000.0));
    }

    /**
     * Test tryConvertToInt with floats that have fractional parts.
     */
    public function testTryConvertToIntWithFractionalNumbers(): void
    {
        $this->assertNull(Floats::tryConvertToInt(5.5));
        $this->assertNull(Floats::tryConvertToInt(1.001));
        $this->assertNull(Floats::tryConvertToInt(-3.14));
    }

    /**
     * Test tryConvertToInt with edge case floats.
     */
    public function testTryConvertToIntEdgeCases(): void
    {
        // Very small positive number (not zero)
        $this->assertNull(Floats::tryConvertToInt(0.1));

        // Very small negative number (not zero)
        $this->assertNull(Floats::tryConvertToInt(-0.1));

        // Negative zero
        $this->assertSame(0, Floats::tryConvertToInt(-0.0));
    }

    /**
     * Test tryConvertToInt with large integers that can be exactly represented as floats.
     */
    public function testTryConvertToIntWithLargeIntegers(): void
    {
        // Use powers of 2 up to 2^53, which can be exactly represented as floats
        $this->assertSame(1 << 50, Floats::tryConvertToInt((float) (1 << 50)));

        // Negative large integer
        $this->assertSame(-(1 << 50), Floats::tryConvertToInt((float) (-(1 << 50))));

        // PHP_INT_MIN is -2^63, which is a power of 2 and CAN be exactly represented as a float
        $this->assertSame(PHP_INT_MIN, Floats::tryConvertToInt((float) PHP_INT_MIN));

        // Note: PHP_INT_MAX (2^63 - 1) cannot be exactly represented as a float
        // because it has many bits set and exceeds the 53-bit mantissa precision
    }

    /**
     * Test tryConvertToInt with floats that lose precision when cast to int.
     */
    public function testTryConvertToIntOutOfRange(): void
    {
        // Float larger than PHP_INT_MAX (loses precision)
        $f = (float) PHP_INT_MAX * 2;
        // Verify it doesn't crash and returns int or null
        /** @var null|int $result */
        $result = Floats::tryConvertToInt($f);
        $this->assertTrue($result === null || is_int($result));
    }

    /**
     * Test tryConvertToInt with PHP_INT_MAX and PHP_INT_MIN boundary values.
     */
    public function testTryConvertToIntWithIntBoundaries(): void
    {
        // (float)PHP_INT_MAX rounds up to 2^63, which overflows int.
        // This must return null without triggering a PHP warning.
        $this->assertNull(Floats::tryConvertToInt((float) PHP_INT_MAX));

        // PHP_INT_MIN is -2^63, exactly representable as a float.
        $this->assertSame(PHP_INT_MIN, Floats::tryConvertToInt((float) PHP_INT_MIN));

        // Largest float that fits in an int: 2^63 - 1024 = 9223372036854774784.
        $largest = 9223372036854774784.0;
        $this->assertSame(9223372036854774784, Floats::tryConvertToInt($largest));

        // One float step above that is 2^63, which should fail.
        $this->assertNull(Floats::tryConvertToInt($largest + 1024.0));
    }

    /**
     * Test tryConvertToInt with various representable integers.
     */
    public function testTryConvertToIntWithVariousIntegers(): void
    {
        $testCases = [
            [1.0, 1],
            [-1.0, -1],
            [100.0, 100],
            [-100.0, -100],
            [0.0, 0],
            [-0.0, 0],
            [42.0, 42],
            [-42.0, -42],
        ];

        foreach ($testCases as [$float, $expectedInt]) {
            $this->assertSame($expectedInt, Floats::tryConvertToInt($float), "Wrong conversion for $float");
        }
    }

    /**
     * Test tryConvertToInt with various non-convertible floats.
     */
    public function testTryConvertToIntWithNonConvertibleFloats(): void
    {
        $testCases = [0.1, 0.5, 0.999, 1.1, -0.5, -1.5, 3.14159, -2.71828];

        foreach ($testCases as $float) {
            $this->assertNull(Floats::tryConvertToInt($float), "Should return null for $float");
        }
    }

    /**
     * Test tryConvertToInt with non-finite floats.
     */
    public function testTryConvertToIntWithNonFiniteFloats(): void
    {
        $this->assertNull(Floats::tryConvertToInt(NAN));
        $this->assertNull(Floats::tryConvertToInt(INF));
        $this->assertNull(Floats::tryConvertToInt(-INF));
    }

    #endregion

    #region Precision method tests

    /**
     * Test isExactInt with whole number floats.
     */
    public function testIsExactIntWithWholeNumbers(): void
    {
        $this->assertTrue(Floats::isExactInt(0.0));
        $this->assertTrue(Floats::isExactInt(1.0));
        $this->assertTrue(Floats::isExactInt(-1.0));
        $this->assertTrue(Floats::isExactInt(42.0));
        $this->assertTrue(Floats::isExactInt(-99.0));
        $this->assertTrue(Floats::isExactInt(1000000.0));
    }

    /**
     * Test isExactInt with fractional floats.
     */
    public function testIsExactIntWithFractionalNumbers(): void
    {
        $this->assertFalse(Floats::isExactInt(0.5));
        $this->assertFalse(Floats::isExactInt(1.1));
        $this->assertFalse(Floats::isExactInt(-3.14));
        $this->assertFalse(Floats::isExactInt(0.001));
        $this->assertFalse(Floats::isExactInt(99.999));
    }

    /**
     * Test isExactInt with negative zero.
     */
    public function testIsExactIntWithNegativeZero(): void
    {
        $this->assertTrue(Floats::isExactInt(-0.0));
    }

    /**
     * Test isExactInt at the boundary of exact representation (2^53).
     */
    public function testIsExactIntAtExactBoundary(): void
    {
        // 2^53 is the largest consecutive integer exactly representable
        $boundary = 1 << 53; // 9007199254740992
        $this->assertTrue(Floats::isExactInt((float) $boundary));
        $this->assertTrue(Floats::isExactInt((float) -$boundary));
    }

    /**
     * Test isExactInt beyond exact representation boundary.
     */
    public function testIsExactIntBeyondBoundary(): void
    {
        // 2^54 is beyond our ±2^53 range
        $this->assertFalse(Floats::isExactInt((float) (1 << 54)));
        $this->assertFalse(Floats::isExactInt((float) (-(1 << 54))));

        // Very large values are beyond the range
        $this->assertFalse(Floats::isExactInt((float) PHP_INT_MAX));
        $this->assertFalse(Floats::isExactInt(1e20));
    }

    /**
     * Test isExactInt with large integers within exact range.
     */
    public function testIsExactIntWithLargeIntegers(): void
    {
        // Powers of 2 up to 2^53
        $this->assertTrue(Floats::isExactInt((float) (1 << 40)));
        $this->assertTrue(Floats::isExactInt((float) (1 << 50)));
        $this->assertTrue(Floats::isExactInt((float) (1 << 52)));
    }

    /**
     * Test isExactInt with non-finite values.
     */
    public function testIsExactIntWithNonFinite(): void
    {
        $this->assertFalse(Floats::isExactInt(INF));
        $this->assertFalse(Floats::isExactInt(-INF));
        $this->assertFalse(Floats::isExactInt(NAN));
    }

    /**
     * Test isExactInt vs tryConvertToInt relationship.
     */
    public function testIsExactIntVsTryConvertToIntRelationship(): void
    {
        // isExactInt checks for exact integer representation within ±2^53
        // tryConvertToInt checks for lossless conversion to PHP int (±2^63-1)

        // Both should agree for small integers
        $testValues = [0.0, 1.0, -1.0, 42.0, -99.0, 1000.0];
        foreach ($testValues as $value) {
            $isExact = Floats::isExactInt($value);
            $canConvert = Floats::tryConvertToInt($value) !== null;
            $this->assertSame($isExact, $canConvert, "Mismatch for $value");
        }

        // Fractional values fail both
        $this->assertFalse(Floats::isExactInt(1.5));
        $this->assertNull(Floats::tryConvertToInt(1.5));
    }

    /**
     * Test isExactInt comprehensive coverage.
     */
    public function testIsExactIntComprehensive(): void
    {
        // Test various integer values within range
        $testValues = [
            [0.0, true],
            [1.0, true],
            [-1.0, true],
            [100.0, true],
            [-100.0, true],
            [(float) (1 << 52), true],
            [(float) (1 << 53), true],
            [(float) (1 << 54), false],
            [0.5, false],
            [1.1, false],
            [1e20, false],
        ];

        foreach ($testValues as [$value, $expected]) {
            $result = Floats::isExactInt($value);
            $this->assertSame(
                $expected,
                $result,
                sprintf('isExactInt(%s) should be %s', $value, $expected ? 'true' : 'false')
            );
        }
    }

    /**
     * Test isApproxInt with exact integers.
     */
    public function testIsApproxIntWithExactIntegers(): void
    {
        $this->assertTrue(Floats::isApproxInt(0.0));
        $this->assertTrue(Floats::isApproxInt(1.0));
        $this->assertTrue(Floats::isApproxInt(-1.0));
        $this->assertTrue(Floats::isApproxInt(42.0));
        $this->assertTrue(Floats::isApproxInt(-99.0));
        $this->assertTrue(Floats::isApproxInt(1000000.0));
    }

    /**
     * Test isApproxInt with values very close to integers.
     */
    public function testIsApproxIntWithNearIntegers(): void
    {
        // These should be approximately integers within default tolerance
        $this->assertTrue(Floats::isApproxInt(3.0000000001));
        $this->assertTrue(Floats::isApproxInt(2.9999999999));
        $this->assertTrue(Floats::isApproxInt(-5.0000000001));
        $this->assertTrue(Floats::isApproxInt(-4.9999999999));
    }

    /**
     * Test isApproxInt with fractional values.
     */
    public function testIsApproxIntWithFractionalValues(): void
    {
        $this->assertFalse(Floats::isApproxInt(0.5));
        $this->assertFalse(Floats::isApproxInt(1.1));
        $this->assertFalse(Floats::isApproxInt(-3.14));
        $this->assertFalse(Floats::isApproxInt(2.5));
        $this->assertFalse(Floats::isApproxInt(0.001));
    }

    /**
     * Test isApproxInt with logarithm results.
     */
    public function testIsApproxIntWithLogarithms(): void
    {
        // log10(1000) should be approximately 3
        $this->assertTrue(Floats::isApproxInt(log10(1000)));

        // log(1000, 10) should be approximately 3
        $this->assertTrue(Floats::isApproxInt(log(1000, 10)));

        // log(1000000, 1000) should be approximately 2
        $this->assertTrue(Floats::isApproxInt(log(1000000, 1000)));

        // log(100, 1000) is not an integer (it's 2/3)
        $this->assertFalse(Floats::isApproxInt(log(100, 1000)));
    }

    /**
     * Test isApproxInt with custom tolerance.
     */
    public function testIsApproxIntWithCustomTolerance(): void
    {
        // With very strict tolerance, near-integers should fail
        $this->assertFalse(Floats::isApproxInt(3.0001, 0.0, 1e-5));

        // With looser tolerance, they should pass
        $this->assertTrue(Floats::isApproxInt(3.0001, 0.0, 1e-3));
    }

    /**
     * Test isApproxInt with non-finite values.
     */
    public function testIsApproxIntWithNonFinite(): void
    {
        $this->assertFalse(Floats::isApproxInt(INF));
        $this->assertFalse(Floats::isApproxInt(-INF));
        $this->assertFalse(Floats::isApproxInt(NAN));
    }

    #endregion

    #region Inspection method tests

    /**
     * Test detection of negative zero.
     */
    public function testIsNegativeZero(): void
    {
        // Test that -0.0 is correctly identified as negative zero.
        $this->assertTrue(Floats::isNegativeZero(-0.0));

        // Test that positive zero is not negative zero.
        $this->assertFalse(Floats::isNegativeZero(0.0));

        // Test that positive values are not negative zero.
        $this->assertFalse(Floats::isNegativeZero(1.0));

        // Test that negative values are not negative zero.
        $this->assertFalse(Floats::isNegativeZero(-1.0));

        // Test that infinity values are not negative zero.
        $this->assertFalse(Floats::isNegativeZero(INF));
        $this->assertFalse(Floats::isNegativeZero(-INF));

        // Test that NAN is not negative zero.
        $this->assertFalse(Floats::isNegativeZero(NAN));
    }

    /**
     * Test detection of positive zero.
     */
    public function testIsPositiveZero(): void
    {
        // Test that +0.0 is correctly identified as positive zero.
        $this->assertTrue(Floats::isPositiveZero(0.0));

        // Test that negative zero is not positive zero.
        $this->assertFalse(Floats::isPositiveZero(-0.0));

        // Test that positive values are not positive zero.
        $this->assertFalse(Floats::isPositiveZero(1.0));

        // Test that negative values are not positive zero.
        $this->assertFalse(Floats::isPositiveZero(-1.0));

        // Test that infinity values are not positive zero.
        $this->assertFalse(Floats::isPositiveZero(INF));
        $this->assertFalse(Floats::isPositiveZero(-INF));

        // Test that NAN is not positive zero.
        $this->assertFalse(Floats::isPositiveZero(NAN));
    }

    /**
     * Test detection of negative values.
     */
    public function testIsNegative(): void
    {
        // Test that negative values are correctly identified.
        $this->assertTrue(Floats::isNegative(-1.0));
        $this->assertTrue(Floats::isNegative(-0.5));
        $this->assertTrue(Floats::isNegative(-100.0));

        // Test that negative zero is identified as negative.
        $this->assertTrue(Floats::isNegative(-0.0));

        // Test that negative infinity is identified as negative.
        $this->assertTrue(Floats::isNegative(-INF));

        // Test that positive values are not negative.
        $this->assertFalse(Floats::isNegative(1.0));
        $this->assertFalse(Floats::isNegative(0.5));

        // Test that positive zero is not negative.
        $this->assertFalse(Floats::isNegative(0.0));

        // Test that positive infinity is not negative.
        $this->assertFalse(Floats::isNegative(INF));

        // Test that NAN is not negative.
        $this->assertFalse(Floats::isNegative(NAN));
    }

    /**
     * Test detection of positive values.
     */
    public function testIsPositive(): void
    {
        // Test that positive values are correctly identified.
        $this->assertTrue(Floats::isPositive(1.0));
        $this->assertTrue(Floats::isPositive(0.5));
        $this->assertTrue(Floats::isPositive(100.0));

        // Test that positive zero is identified as positive.
        $this->assertTrue(Floats::isPositive(0.0));

        // Test that positive infinity is identified as positive.
        $this->assertTrue(Floats::isPositive(INF));

        // Test that negative values are not positive.
        $this->assertFalse(Floats::isPositive(-1.0));
        $this->assertFalse(Floats::isPositive(-0.5));

        // Test that negative zero is not positive.
        $this->assertFalse(Floats::isPositive(-0.0));

        // Test that negative infinity is not positive.
        $this->assertFalse(Floats::isPositive(-INF));

        // Test that NAN is not positive.
        $this->assertFalse(Floats::isPositive(NAN));
    }

    /**
     * Test detection of special float values.
     */
    public function testIsSpecial(): void
    {
        // Test that NAN is identified as special.
        $this->assertTrue(Floats::isSpecial(NAN));

        // Test that negative zero is identified as special.
        $this->assertTrue(Floats::isSpecial(-0.0));

        // Test that positive infinity is identified as special.
        $this->assertTrue(Floats::isSpecial(INF));

        // Test that negative infinity is identified as special.
        $this->assertTrue(Floats::isSpecial(-INF));

        // Test that positive zero is not special.
        $this->assertFalse(Floats::isSpecial(0.0));

        // Test that regular positive values are not special.
        $this->assertFalse(Floats::isSpecial(1.0));
        $this->assertFalse(Floats::isSpecial(42.5));

        // Test that regular negative values are not special.
        $this->assertFalse(Floats::isSpecial(-1.0));
        $this->assertFalse(Floats::isSpecial(-42.5));
    }

    #endregion

    #region format() tests

    /**
     * Test format() with default parameters trims trailing zeros.
     */
    public function testFormatDefaultTrimsZeros(): void
    {
        $this->assertSame('5', Floats::format(5.0));
    }

    /**
     * Test format() with explicit precision preserves trailing zeros.
     */
    public function testFormatExplicitPrecisionPreservesZeros(): void
    {
        $this->assertSame('5.00', Floats::format(5.0, 'f', 2));
    }

    /**
     * Test format() with explicit precision and trimZeros true forces trimming.
     */
    public function testFormatExplicitPrecisionWithTrimZerosTrue(): void
    {
        $this->assertSame('5', Floats::format(5.0, 'f', 2, true));
    }

    /**
     * Test format() with null precision and trimZeros false preserves zeros.
     */
    public function testFormatNullPrecisionWithTrimZerosFalse(): void
    {
        $this->assertSame('5.000000', Floats::format(5.0, 'f', null, false));
    }

    /**
     * Test format() normalizes negative zero.
     */
    public function testFormatNormalizesNegativeZero(): void
    {
        $this->assertSame('0', Floats::format(-0.0));
    }

    /**
     * Test format() with scientific notation and explicit precision preserves zeros.
     */
    public function testFormatScientificPrecisionPreservesZeros(): void
    {
        $this->assertSame('3.0000e+3', Floats::format(3000.0, 'e', 4, ascii: true));
    }

    /**
     * Test format() with scientific notation and null precision trims zeros.
     */
    public function testFormatScientificNullPrecisionTrimsZeros(): void
    {
        $this->assertSame('3e+3', Floats::format(3000.0, 'e', ascii: true));
    }

    /**
     * Test format() with scientific notation and ASCII output.
     */
    public function testFormatScientificAscii(): void
    {
        $this->assertSame('1.50e+3', Floats::format(1500.0, 'e', 2, ascii: true));
    }

    /**
     * Test format() with scientific notation and Unicode output (ascii: false, the default).
     *
     * Covers the `!$ascii` branch in format() that replaces the `e+N` exponent with `×10ⁿ`
     * using superscript digits.
     */
    public function testFormatScientificUnicode(): void
    {
        // Positive exponent.
        $this->assertSame('1.50×10³', Floats::format(1500.0, 'e', 2));

        // Negative exponent — minus sign also becomes a superscript.
        $this->assertSame('2.5×10⁻³', Floats::format(0.0025, 'e', 1));

        // Zero exponent.
        $this->assertSame('1×10⁰', Floats::format(1.0, 'e', 0));

        // Multi-digit exponent.
        $this->assertSame('1×10²³', Floats::format(1e23, 'e', 0));

        // The 'g' specifier should also use Unicode when the value is large enough to trigger
        // exponential form.
        $this->assertSame('1.234568×10⁷', Floats::format(12345678.9, 'g'));
    }

    /**
     * Test format() with invalid specifier throws exception.
     */
    public function testFormatInvalidSpecifierThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Floats::format(1.0, 'x');
    }

    /**
     * Test format() with invalid precision throws exception.
     */
    public function testFormatInvalidPrecisionThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Floats::format(1.0, 'f', -1);
    }

    /**
     * Test format() trimming strips decimal trailing zeros but not integer zeros.
     */
    public function testFormatTrimStripsDecimalButNotIntegerZeros(): void
    {
        // f specifier with precision 2 on 1500.0 produces "1500.00".
        // With trimming, the ".00" is removed but "1500" is preserved.
        $this->assertSame('1500', Floats::format(1500.0, 'f', 2, true));
    }

    #endregion
}
