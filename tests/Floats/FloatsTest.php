<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Floats;

use DomainException;
use OceanMoon\Core\ExponentFormat;
use OceanMoon\Core\FloatFormat;
use OceanMoon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RoundingMode;

use const OceanMoon\Core\M_TAU;

/**
 * Test class for Floats utility class - core comparison, transformation, precision, and inspection methods.
 */
#[CoversClass(Floats::class)]
#[CoversClass(ExponentFormat::class)]
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
        $this->expectExceptionMessage('Invalid relative tolerance: -0.1. Must be finite and non-negative.');
        Floats::approxEqual(1.0, 1.0, -0.1, 0.0);
    }

    /**
     * Test approxEqual with a negative absolute tolerance throws DomainException.
     */
    public function testApproxEqualWithNegativeAbsoluteToleranceThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid absolute tolerance: -1.0. Must be finite and non-negative.');
        Floats::approxEqual(1.0, 1.0, 0.0, -1.0);
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
        $this->expectExceptionMessage('Invalid relative tolerance: -0.1. Must be finite and non-negative.');
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
     * Test Floats::trunc() with positive values.
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
     * Test Floats::trunc() with negative values.
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
     * Test Floats::trunc() with zero values.
     */
    public function testTruncZero(): void
    {
        $this->assertSame(0.0, Floats::trunc(0.0));
        $this->assertSame(0.0, Floats::trunc(-0.0));
        $this->assertSame(0.0, Floats::trunc(0.5));
        $this->assertSame(0.0, Floats::trunc(-0.5));
    }

    /**
     * Test Floats::trunc() with non-finite values.
     */
    public function testTruncNonFinite(): void
    {
        $this->assertSame(INF, Floats::trunc(INF));
        $this->assertSame(-INF, Floats::trunc(-INF));
        $this->assertTrue(is_nan(Floats::trunc(NAN)));
    }

    /**
     * Test Floats::trunc() differs from floor() for negative values.
     */
    public function testTruncDiffersFromFloor(): void
    {
        // floor() rounds toward -INF, Floats::trunc() rounds toward zero
        $this->assertSame(-3.0, Floats::trunc(-3.7));
        $this->assertSame(-4.0, floor(-3.7));

        $this->assertSame(-3.0, Floats::trunc(-3.2));
        $this->assertSame(-4.0, floor(-3.2));
    }

    #endregion

    #region Floats::frac() tests.

    /**
     * Test Floats::frac() with positive values.
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
     * Test Floats::frac() with negative values.
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
     * Test Floats::frac() with zero values.
     */
    public function testFracZero(): void
    {
        $this->assertSame(0.0, Floats::frac(0.0));
        $this->assertSame(0.0, Floats::frac(-0.0));
    }

    /**
     * Test Floats::frac() with non-finite values.
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
     * Test Floats::frac() satisfies the identity x = Floats::trunc(x) + Floats::frac(x).
     */
    public function testFracIdentity(): void
    {
        $testValues = [3.7, -3.7, 0.5, -0.5, 100.999, -100.999, 0.0, 42.0, -42.0];

        foreach ($testValues as $value) {
            $this->assertEqualsWithDelta(
                $value,
                Floats::trunc($value) + Floats::frac($value),
                1e-10,
                "Identity x = Floats::trunc(x) + Floats::frac(x) failed for x = $value"
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
        $this->assertEqualsWithDelta(0.0, Floats::wrap(M_TAU), 1e-10);
        $this->assertEqualsWithDelta(-M_PI / 2, Floats::wrap(3 * M_PI / 2), 1e-10);

        // Unsigned: range is [0, τ).
        $this->assertSame(0.0, Floats::wrap(0.0, signed: false));
        $this->assertSame(M_PI, Floats::wrap(M_PI, signed: false));
        $this->assertSame(M_PI, Floats::wrap(-M_PI, signed: false));
        $this->assertEqualsWithDelta(0.0, Floats::wrap(M_TAU, signed: false), 1e-10);
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
     * Test toInt with floats that equal whole numbers.
     */
    public function testToIntWithWholeNumbers(): void
    {
        $this->assertSame(5, Floats::toInt(5.0));
        $this->assertSame(-10, Floats::toInt(-10.0));
        $this->assertSame(0, Floats::toInt(0.0));
        $this->assertSame(1000000, Floats::toInt(1000000.0));
    }

    /**
     * Test toInt with floats that have fractional parts throws.
     */
    public function testToIntWithFractionalNumbersThrows(): void
    {
        foreach ([5.5, 1.001, -3.14] as $float) {
            try {
                Floats::toInt($float);
                $this->fail("Expected DomainException for $float");
            } catch (DomainException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Test toInt with edge case floats.
     */
    public function testToIntEdgeCases(): void
    {
        // Very small positive number (not zero) - throws.
        try {
            Floats::toInt(0.1);
            $this->fail('Expected DomainException for 0.1');
        } catch (DomainException) {
            $this->addToAssertionCount(1);
        }

        // Very small negative number (not zero) - throws.
        try {
            Floats::toInt(-0.1);
            $this->fail('Expected DomainException for -0.1');
        } catch (DomainException) {
            $this->addToAssertionCount(1);
        }

        // Negative zero converts to int 0.
        $this->assertSame(0, Floats::toInt(-0.0));
    }

    /**
     * Test toInt with large integers that can be exactly represented as floats.
     */
    public function testToIntWithLargeIntegers(): void
    {
        // Use powers of 2 up to 2^53, which can be exactly represented as floats
        $this->assertSame(1 << 50, Floats::toInt((float) (1 << 50)));

        // Negative large integer
        $this->assertSame(-(1 << 50), Floats::toInt((float) (-(1 << 50))));

        // PHP_INT_MIN is -2^63, which is a power of 2 and CAN be exactly represented as a float
        $this->assertSame(PHP_INT_MIN, Floats::toInt((float) PHP_INT_MIN));

        // Note: PHP_INT_MAX (2^63 - 1) cannot be exactly represented as a float
        // because it has many bits set and exceeds the 53-bit mantissa precision
    }

    /**
     * Test toInt with a float that loses precision when cast to int.
     */
    public function testToIntOutOfRange(): void
    {
        // Float larger than PHP_INT_MAX (loses precision). Verify it doesn't crash: either succeeds (return type
        // already guarantees int), or throws DomainException.
        $f = (float) PHP_INT_MAX * 2;
        try {
            Floats::toInt($f);
            $this->addToAssertionCount(1);
        } catch (DomainException) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * Test toInt with PHP_INT_MIN and the largest representable boundary value.
     */
    public function testToIntWithIntBoundaries(): void
    {
        // PHP_INT_MIN is -2^63, exactly representable as a float.
        $this->assertSame(PHP_INT_MIN, Floats::toInt((float) PHP_INT_MIN));

        // Largest float that fits in an int: 2^63 - 1024 = 9223372036854774784.
        $largest = 9223372036854774784.0;
        $this->assertSame(9223372036854774784, Floats::toInt($largest));
    }

    /**
     * Test toInt with (float) PHP_INT_MAX throws, since it rounds up to 2^63 which overflows int. Must throw
     * without triggering a PHP warning.
     */
    public function testToIntWithPhpIntMaxThrows(): void
    {
        $this->expectException(DomainException::class);
        Floats::toInt((float) PHP_INT_MAX);
    }

    /**
     * Test toInt with the float one step above the largest representable int-as-float value (2^63) throws.
     */
    public function testToIntJustAboveLargestRepresentableThrows(): void
    {
        $largest = 9223372036854774784.0;
        $this->expectException(DomainException::class);
        Floats::toInt($largest + 1024.0);
    }

    /**
     * Test toInt with various representable integers.
     */
    public function testToIntWithVariousIntegers(): void
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
            $this->assertSame($expectedInt, Floats::toInt($float), "Wrong conversion for $float");
        }
    }

    /**
     * Test toInt with various non-convertible floats throws for each.
     */
    public function testToIntWithNonConvertibleFloatsThrows(): void
    {
        $testCases = [0.1, 0.5, 0.999, 1.1, -0.5, -1.5, 3.14159, -2.71828];

        foreach ($testCases as $float) {
            try {
                Floats::toInt($float);
                $this->fail("Expected DomainException for $float");
            } catch (DomainException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Test toInt with non-finite floats throws for each.
     */
    public function testToIntWithNonFiniteFloatsThrows(): void
    {
        foreach ([NAN, INF, -INF] as $float) {
            try {
                Floats::toInt($float);
                $this->fail("Expected DomainException for $float");
            } catch (DomainException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    #endregion

    #region Integer method tests

    /**
     * Test isInt with whole number floats.
     */
    public function testIsIntWithWholeNumbers(): void
    {
        $this->assertTrue(Floats::isInt(0.0));
        $this->assertTrue(Floats::isInt(1.0));
        $this->assertTrue(Floats::isInt(-1.0));
        $this->assertTrue(Floats::isInt(42.0));
        $this->assertTrue(Floats::isInt(1e20));
    }

    /**
     * Test isInt with actual int arguments (not just whole-number floats). Regression test: the parameter was
     * previously untyped, so a plain int slipped through unconverted and floor($value) === $value failed (float
     * !== int under strict comparison), wrongly returning false.
     */
    public function testIsIntWithActualIntegers(): void
    {
        $this->assertTrue(Floats::isInt(0));
        $this->assertTrue(Floats::isInt(1));
        $this->assertTrue(Floats::isInt(-1));
        $this->assertTrue(Floats::isInt(42));
        $this->assertTrue(Floats::isInt(PHP_INT_MAX));
        $this->assertTrue(Floats::isInt(PHP_INT_MIN));
    }

    /**
     * Test isInt with fractional floats.
     */
    public function testIsIntWithFractionalNumbers(): void
    {
        $this->assertFalse(Floats::isInt(0.5));
        $this->assertFalse(Floats::isInt(1.1));
        $this->assertFalse(Floats::isInt(-3.14));
    }

    /**
     * Test isInt with negative zero.
     */
    public function testIsIntWithNegativeZero(): void
    {
        $this->assertTrue(Floats::isInt(-0.0));
    }

    /**
     * Test isInt with non-finite values.
     */
    public function testIsIntWithNonFinite(): void
    {
        $this->assertFalse(Floats::isInt(INF));
        $this->assertFalse(Floats::isInt(-INF));
        $this->assertFalse(Floats::isInt(NAN));
    }

    /**
     * Test isInt has no numeric upper bound, unlike isSafeInt() - 1e20 is well beyond MAX_SAFE_INT but is still
     * numerically a whole number.
     */
    public function testIsIntHasNoUpperBound(): void
    {
        $this->assertTrue(Floats::isInt(1e20));
        $this->assertFalse(Floats::isSafeInt(1e20));
    }

    /**
     * Test isSafeInt with whole number floats.
     */
    public function testIsSafeIntWithWholeNumbers(): void
    {
        $this->assertTrue(Floats::isSafeInt(0.0));
        $this->assertTrue(Floats::isSafeInt(1.0));
        $this->assertTrue(Floats::isSafeInt(-1.0));
        $this->assertTrue(Floats::isSafeInt(42.0));
        $this->assertTrue(Floats::isSafeInt(-99.0));
        $this->assertTrue(Floats::isSafeInt(1000000.0));
    }

    /**
     * Test isSafeInt with fractional floats.
     */
    public function testIsSafeIntWithFractionalNumbers(): void
    {
        $this->assertFalse(Floats::isSafeInt(0.5));
        $this->assertFalse(Floats::isSafeInt(1.1));
        $this->assertFalse(Floats::isSafeInt(-3.14));
        $this->assertFalse(Floats::isSafeInt(0.001));
        $this->assertFalse(Floats::isSafeInt(99.999));
    }

    /**
     * Test isSafeInt with negative zero.
     */
    public function testIsSafeIntWithNegativeZero(): void
    {
        $this->assertTrue(Floats::isSafeInt(-0.0));
    }

    /**
     * Test isSafeInt at the boundary of the safe-integer range (2^53 - 1), matching JavaScript's
     * Number.isSafeInteger() semantics: 2^53 itself is excluded, even though it's still exactly representable, since
     * 2^53 + 1 would round down to 2^53 and collide with it.
     */
    public function testIsSafeIntAtSafeBoundary(): void
    {
        $maxSafe = (1 << 53) - 1; // 9007199254740991
        $this->assertTrue(Floats::isSafeInt((float) $maxSafe));
        $this->assertTrue(Floats::isSafeInt((float) -$maxSafe));

        // 2^53 is exactly representable but is one past the safe boundary.
        $unsafe = 1 << 53; // 9007199254740992
        $this->assertFalse(Floats::isSafeInt((float) $unsafe));
        $this->assertFalse(Floats::isSafeInt((float) -$unsafe));
    }

    /**
     * Test isSafeInt beyond the safe-integer boundary.
     */
    public function testIsSafeIntBeyondBoundary(): void
    {
        // 2^54 is beyond the safe range
        $this->assertFalse(Floats::isSafeInt((float) (1 << 54)));
        $this->assertFalse(Floats::isSafeInt((float) (-(1 << 54))));

        // Very large values are beyond the range
        $this->assertFalse(Floats::isSafeInt((float) PHP_INT_MAX));
        $this->assertFalse(Floats::isSafeInt(1e20));
    }

    /**
     * Test isSafeInt with large integers within the safe range.
     */
    public function testIsSafeIntWithLargeIntegers(): void
    {
        // Powers of 2 well below 2^53 - 1
        $this->assertTrue(Floats::isSafeInt((float) (1 << 40)));
        $this->assertTrue(Floats::isSafeInt((float) (1 << 50)));
        $this->assertTrue(Floats::isSafeInt((float) (1 << 52)));
    }

    /**
     * Test isSafeInt with non-finite values.
     */
    public function testIsSafeIntWithNonFinite(): void
    {
        $this->assertFalse(Floats::isSafeInt(INF));
        $this->assertFalse(Floats::isSafeInt(-INF));
        $this->assertFalse(Floats::isSafeInt(NAN));
    }

    /**
     * Test isSafeInt vs toInt relationship.
     */
    public function testIsSafeIntVsToIntRelationship(): void
    {
        // isSafeInt checks for safe integer representation within ±(2^53 - 1)
        // toInt checks for lossless conversion to PHP int (±(2^63 - 1)), throwing on failure

        // Both should agree for small integers, well within both ranges.
        $testValues = [0.0, 1.0, -1.0, 42.0, -99.0, 1000.0];
        foreach ($testValues as $value) {
            $isSafe = Floats::isSafeInt($value);
            try {
                Floats::toInt($value);
                $canConvert = true;
            } catch (DomainException) {
                $canConvert = false;
            }
            $this->assertSame($isSafe, $canConvert, "Mismatch for $value");
        }

        // Fractional values fail both.
        $this->assertFalse(Floats::isSafeInt(1.5));
        $this->expectException(DomainException::class);
        Floats::toInt(1.5);
    }

    /**
     * Test isSafeInt comprehensive coverage.
     */
    public function testIsSafeIntComprehensive(): void
    {
        // Test various integer values within range
        $testValues = [
            [0.0, true],
            [1.0, true],
            [-1.0, true],
            [100.0, true],
            [-100.0, true],
            [
                (float) (1 << 52),
                true,
            ],
            [
                (float) ((1 << 53) - 1),
                true,
            ],
            [
                (float) (1 << 53),
                false,
            ],
            [
                (float) (1 << 54),
                false,
            ],
            [0.5, false],
            [1.1, false],
            [1e20, false],
        ];

        foreach ($testValues as [$value, $expected]) {
            $result = Floats::isSafeInt($value);
            $this->assertSame(
                $expected,
                $result,
                sprintf('isSafeInt(%s) should be %s', $value, $expected ? 'true' : 'false')
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

    #region Method getExponent() tests.

    /**
     * Test getExponent() with typical positive values at various magnitudes.
     */
    public function testGetExponentWithPositiveValues(): void
    {
        $this->assertSame(0, Floats::getExponent(5.0));
        $this->assertSame(1, Floats::getExponent(50.0));
        $this->assertSame(2, Floats::getExponent(500.0));
        $this->assertSame(-1, Floats::getExponent(0.5));
        $this->assertSame(-2, Floats::getExponent(0.05));
    }

    /**
     * Test getExponent() with negative values returns the same exponent as the equivalent positive value.
     */
    public function testGetExponentWithNegativeValues(): void
    {
        $this->assertSame(3, Floats::getExponent(-1234.0));
        $this->assertSame(3, Floats::getExponent(1234.0));
    }

    /**
     * Test getExponent() with 0.0 returns 0 rather than -INF.
     */
    public function testGetExponentWithZero(): void
    {
        $this->assertSame(0, Floats::getExponent(0.0));
    }

    /**
     * Test getExponent() with exact powers of 10, guarding against log10() rounding error (e.g. some platforms
     * return log10(1000) as 2.9999999999999996 rather than exactly 3.0).
     */
    public function testGetExponentWithExactPowersOfTen(): void
    {
        $this->assertSame(3, Floats::getExponent(1000.0));
        $this->assertSame(-3, Floats::getExponent(0.001));
        $this->assertSame(21, Floats::getExponent(1.0e21));
        $this->assertSame(-7, Floats::getExponent(1.0e-7));
    }

    /**
     * Test getExponent() with values just below and above a power-of-ten boundary.
     */
    public function testGetExponentNearBoundary(): void
    {
        $this->assertSame(0, Floats::getExponent(9.999999));
        $this->assertSame(1, Floats::getExponent(10.00001));
    }

    /**
     * Test getExponent() with a value one ULP below an exact power of ten, where log10() actually overestimates
     * the exponent on this platform (e.g. log10() of the double just below 1.0e15 returns exactly 15.0 rather
     * than a value just under it), exercising the mantissa < 1.0 correction.
     */
    public function testGetExponentWithLog10OverestimateJustBelowPowerOfTen(): void
    {
        $this->assertSame(14, Floats::getExponent(999999999999999.9));
    }

    #endregion

    #region Method format() tests.

    /**
     * Test format() with defaults (Auto, precision 6, trimZeros true) for typical values.
     */
    public function testFormatDefaults(): void
    {
        $this->assertSame('1234.5', Floats::format(1234.5));
        $this->assertSame('5', Floats::format(5.0));
    }

    /**
     * Test format() normalizes negative zero.
     */
    public function testFormatNormalizesNegativeZero(): void
    {
        $this->assertSame('0', Floats::format(-0.0));
    }

    /**
     * Test format() with NAN/±INF returns the default PHP string representation, regardless of other
     * parameters, and without triggering the warning a direct (string) cast on NAN would emit.
     */
    public function testFormatWithNonFiniteValues(): void
    {
        $this->assertSame('NAN', Floats::format(NAN));
        $this->assertSame('INF', Floats::format(INF));
        $this->assertSame('-INF', Floats::format(-INF));
    }

    /**
     * Test format() with FloatFormat::FixedPoint. $precision means decimal places.
     */
    public function testFormatFixedPoint(): void
    {
        $this->assertSame('5', Floats::format(5.0, precision: 2, format: FloatFormat::FixedPoint));
        $this->assertSame(
            '5.00',
            Floats::format(5.0, precision: 2, trimZeros: false, format: FloatFormat::FixedPoint)
        );
        $this->assertSame('1500', Floats::format(1500.0, precision: 2, format: FloatFormat::FixedPoint));
    }

    /**
     * Test format() with FloatFormat::Scientific. $precision means significant digits, and the result always
     * includes an exponent, even when the value doesn't need one.
     */
    public function testFormatScientific(): void
    {
        $this->assertSame('1.5×10³', Floats::format(1500.0, format: FloatFormat::Scientific));
        $this->assertSame('2.5×10⁻³', Floats::format(0.0025, precision: 2, format: FloatFormat::Scientific));

        // 1000.0 at 3 significant figures needs two padding zeros ('1.00'), unlike 1500.0 at precision 2
        // ('1.5'), which has no padding to preserve since both digits are already significant.
        $this->assertSame(
            '1×10³',
            Floats::format(1000.0, precision: 3, format: FloatFormat::Scientific)
        );
        $this->assertSame(
            '1.00×10³',
            Floats::format(1000.0, precision: 3, trimZeros: false, format: FloatFormat::Scientific)
        );
    }

    /**
     * Test format() with each ExponentFormat case.
     */
    public function testFormatExponentFormats(): void
    {
        $this->assertSame(
            '1.5e+3',
            Floats::format(
                1500.0,
                precision: 2,
                format: FloatFormat::Scientific,
                expFormat: ExponentFormat::AsciiLowerCaseE
            )
        );
        $this->assertSame(
            '1.5E+3',
            Floats::format(
                1500.0,
                precision: 2,
                format: FloatFormat::Scientific,
                expFormat: ExponentFormat::AsciiUpperCaseE
            )
        );
        $this->assertSame(
            '1.5*10^3',
            Floats::format(1500.0, precision: 2, format: FloatFormat::Scientific, expFormat: ExponentFormat::AsciiMath)
        );
        $this->assertSame(
            '1.5×10³',
            Floats::format(
                1500.0,
                precision: 2,
                format: FloatFormat::Scientific,
                expFormat: ExponentFormat::UnicodeMath
            )
        );
        $this->assertSame(
            '1.5&times;10<sup>3</sup>',
            Floats::format(1500.0, precision: 2, format: FloatFormat::Scientific, expFormat: ExponentFormat::HtmlMath)
        );
    }

    /**
     * Test format() with FloatFormat::Auto prefers fixed-point when it preserves more real information than
     * scientific notation would (i.e. the value isn't actually round, it just has more digits than $precision).
     */
    public function testFormatAutoPrefersFixedPointWhenMoreInformative(): void
    {
        // Has more real significant digits than the default precision (6) would show in scientific notation,
        // so fixed-point wins even though the string is longer.
        $this->assertSame('1234567.891', Floats::format(1234567.891));
    }

    /**
     * Test format() with FloatFormat::Auto switches to scientific notation for genuinely round/large numbers,
     * where fixed-point would need excessive trailing zeros.
     */
    public function testFormatAutoPrefersScientificForRoundNumbers(): void
    {
        $this->assertSame('5×10⁹', Floats::format(5000000000.0));
    }

    /**
     * Test format() with FloatFormat::Auto switches to scientific notation for small numbers with excessive
     * leading zeros, and behaves identically regardless of sign.
     */
    public function testFormatAutoPrefersScientificForSmallNumbers(): void
    {
        $this->assertSame('1.234×10⁻⁴', Floats::format(0.0001234));
        $this->assertSame('-1.234×10⁻⁴', Floats::format(-0.0001234));
    }

    /**
     * Test format() with RoundingMode. Defaults to HalfAwayFromZero (matching round(), Rational::round(), and
     * Complex::round()), not sprintf()'s round-half-to-even.
     */
    public function testFormatRoundingMode(): void
    {
        $this->assertSame(
            '1',
            Floats::format(0.5, precision: 0, format: FloatFormat::FixedPoint)
        );
        $this->assertSame(
            '0',
            Floats::format(0.5, precision: 0, format: FloatFormat::FixedPoint, roundingMode: RoundingMode::HalfEven)
        );
        $this->assertSame(
            '-0',
            Floats::format(
                -0.5,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::HalfTowardsZero
            )
        );
    }

    /**
     * Test format() with RoundingMode::HalfOdd rounds a tie to the nearest odd digit, unlike HalfEven or
     * HalfAwayFromZero.
     */
    public function testFormatRoundingModeHalfOdd(): void
    {
        $this->assertSame(
            '1',
            Floats::format(1.5, precision: 0, format: FloatFormat::FixedPoint, roundingMode: RoundingMode::HalfOdd)
        );
        $this->assertSame(
            '3',
            Floats::format(2.5, precision: 0, format: FloatFormat::FixedPoint, roundingMode: RoundingMode::HalfOdd)
        );
    }

    /**
     * Test format() with RoundingMode::TowardsZero truncates, rather than rounding, both positive and negative
     * values.
     */
    public function testFormatRoundingModeTowardsZero(): void
    {
        $this->assertSame(
            '0',
            Floats::format(
                0.5,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::TowardsZero
            )
        );
        $this->assertSame(
            '-0',
            Floats::format(
                -0.5,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::TowardsZero
            )
        );
    }

    /**
     * Test format() with RoundingMode::AwayFromZero rounds up in magnitude, rather than towards the nearer integer,
     * for both positive and negative values.
     */
    public function testFormatRoundingModeAwayFromZero(): void
    {
        $this->assertSame(
            '1',
            Floats::format(
                0.5,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::AwayFromZero
            )
        );
        $this->assertSame(
            '-1',
            Floats::format(
                -0.5,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::AwayFromZero
            )
        );
    }

    /**
     * Test format() with RoundingMode::NegativeInfinity always rounds down (towards -INF), regardless of sign or
     * whether the value is a tie.
     */
    public function testFormatRoundingModeNegativeInfinity(): void
    {
        $this->assertSame(
            '1',
            Floats::format(
                1.9,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::NegativeInfinity
            )
        );
        $this->assertSame(
            '-2',
            Floats::format(
                -1.9,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::NegativeInfinity
            )
        );
    }

    /**
     * Test format() with RoundingMode::PositiveInfinity always rounds up (towards +INF), regardless of sign or
     * whether the value is a tie.
     */
    public function testFormatRoundingModePositiveInfinity(): void
    {
        $this->assertSame(
            '2',
            Floats::format(
                1.1,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::PositiveInfinity
            )
        );
        $this->assertSame(
            '-1',
            Floats::format(
                -1.1,
                precision: 0,
                format: FloatFormat::FixedPoint,
                roundingMode: RoundingMode::PositiveInfinity
            )
        );
    }

    /**
     * Test format() with an invalid precision for FloatFormat::FixedPoint (valid range [0, 17]) throws.
     */
    public function testFormatInvalidFixedPointPrecisionThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid number of decimal places: -1. Must be between 0 and 17.');
        Floats::format(1.0, precision: -1, format: FloatFormat::FixedPoint);
    }

    /**
     * Test format() with an invalid precision for FloatFormat::Scientific/Auto (valid range [1, 17], excluding
     * 0 since 0 significant digits isn't meaningful) throws.
     */
    public function testFormatInvalidScientificPrecisionThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid number of significant figures: 0. Must be between 1 and 17.');
        Floats::format(1.0, precision: 0, format: FloatFormat::Scientific);
    }

    #endregion
}
