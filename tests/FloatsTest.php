<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Floats;
use Galaxon\Core\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Test class for Floats utility class.
 */
#[CoversClass(Floats::class)]
final class FloatsTest extends TestCase
{
    // region approxEqual tests

    /**
     * Test approxEqual with identical values.
     */
    public function testApproxEqualWithIdenticalValues(): void
    {
        $this->assertTrue(Floats::approxEqual(1.0, 1.0));
        $this->assertTrue(Floats::approxEqual(0.0, 0.0));
        $this->assertTrue(Floats::approxEqual(-5.5, -5.5));
        $this->assertTrue(Floats::approxEqual(1e100, 1e100));
    }

    /**
     * Test approxEqual with values within default epsilon.
     */
    public function testApproxEqualWithinDefaultEpsilon(): void
    {
        // Default epsilon is 1e-10
        $this->assertTrue(Floats::approxEqual(1.0, 1.0 + 1e-11));
        $this->assertTrue(Floats::approxEqual(1.0, 1.0 - 1e-11));
        $this->assertTrue(Floats::approxEqual(0.0, 1e-11));
        $this->assertTrue(Floats::approxEqual(0.0, -1e-11));
    }

    /**
     * Test approxEqual with values outside default epsilon.
     */
    public function testApproxEqualOutsideDefaultEpsilon(): void
    {
        // Default epsilon is 1e-10
        $this->assertFalse(Floats::approxEqual(1.0, 1.0 + 1e-9));
        $this->assertFalse(Floats::approxEqual(1.0, 1.0 - 1e-9));
        $this->assertFalse(Floats::approxEqual(0.0, 1e-9));
    }

    /**
     * Test approxEqual with custom epsilon.
     */
    public function testApproxEqualWithCustomEpsilon(): void
    {
        // Use a larger epsilon
        $this->assertTrue(Floats::approxEqual(1.0, 1.1, 0.2));
        $this->assertTrue(Floats::approxEqual(1.0, 0.9, 0.2));
        $this->assertFalse(Floats::approxEqual(1.0, 1.3, 0.2));

        // Use a smaller epsilon
        $this->assertFalse(Floats::approxEqual(1.0, 1.0 + 1e-15, 1e-16));
        $this->assertTrue(Floats::approxEqual(1.0, 1.0 + 1e-17, 1e-16));
    }

    /**
     * Test approxEqual with zero epsilon (exact equality).
     */
    public function testApproxEqualWithZeroEpsilon(): void
    {
        $this->assertTrue(Floats::approxEqual(1.0, 1.0, 0.0));
        $this->assertFalse(Floats::approxEqual(1.0, 1.0 + PHP_FLOAT_EPSILON, 0.0));
    }

    /**
     * Test approxEqual with negative epsilon throws ValueError.
     */
    public function testApproxEqualWithNegativeEpsilonThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Epsilon must be non-negative');
        Floats::approxEqual(1.0, 1.0, -0.1);
    }

    /**
     * Test approxEqual with negative values.
     */
    public function testApproxEqualWithNegativeValues(): void
    {
        $this->assertTrue(Floats::approxEqual(-1.0, -1.0));
        $this->assertTrue(Floats::approxEqual(-1.0, -1.0 + 1e-11));
        $this->assertTrue(Floats::approxEqual(-1.0, -1.0 - 1e-11));
        $this->assertFalse(Floats::approxEqual(-1.0, -2.0));
    }

    /**
     * Test approxEqual with values of opposite sign.
     */
    public function testApproxEqualWithOppositeSign(): void
    {
        $this->assertFalse(Floats::approxEqual(1.0, -1.0));
        $this->assertFalse(Floats::approxEqual(-1.0, 1.0));
        // But close to zero with appropriate epsilon
        $this->assertTrue(Floats::approxEqual(0.05, -0.05, 0.2));
    }

    /**
     * Test approxEqual with very large values.
     */
    public function testApproxEqualWithLargeValues(): void
    {
        $large = 1e15;
        $this->assertTrue(Floats::approxEqual($large, $large));
        $this->assertTrue(Floats::approxEqual($large, $large + 1e-11));
        // Note: at large magnitudes, the absolute difference may still be tiny
        $this->assertFalse(Floats::approxEqual($large, $large + 1.0));
    }

    /**
     * Test approxEqual with very small values.
     */
    public function testApproxEqualWithSmallValues(): void
    {
        $small = 1e-15;
        $this->assertTrue(Floats::approxEqual($small, $small));
        $this->assertTrue(Floats::approxEqual($small, $small + 1e-26));
        $this->assertFalse(Floats::approxEqual($small, $small * 2, 1e-16));
    }

    /**
     * Test approxEqual symmetry (order of arguments shouldn't matter).
     */
    public function testApproxEqualSymmetry(): void
    {
        $a = 1.0;
        $b = 1.0 + 1e-11;

        $this->assertSame(
            Floats::approxEqual($a, $b),
            Floats::approxEqual($b, $a)
        );

        $this->assertSame(
            Floats::approxEqual($a, $b, 1e-12),
            Floats::approxEqual($b, $a, 1e-12)
        );
    }

    /**
     * Test approxEqual at boundary of epsilon.
     */
    public function testApproxEqualAtBoundary(): void
    {
        $epsilon = 0.1;

        // Exactly at epsilon - should be false (uses < not <=)
        $this->assertFalse(Floats::approxEqual(1.0, 1.1, $epsilon));

        // Just under epsilon - should be true
        $this->assertTrue(Floats::approxEqual(1.0, 1.0999999, $epsilon));
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

    // endregion

    // region isNegativeZero tests

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

        // Test that NaN is not negative zero.
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

        // Test that NaN is not positive zero.
        $this->assertFalse(Floats::isPositiveZero(NAN));
    }

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

        // Test that NaN is unchanged (NaN !== NaN, so use is_nan).
        $this->assertTrue(is_nan(Floats::normalizeZero(NAN)));
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

        // Test that NaN is not negative.
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

        // Test that NaN is not positive.
        $this->assertFalse(Floats::isPositive(NAN));
    }

    /**
     * Test detection of special float values.
     */
    public function testIsSpecial(): void
    {
        // Test that NaN is identified as special.
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
        $this->assertSame(1 << 50, Floats::tryConvertToInt((float)(1 << 50)));

        // Negative large integer
        $this->assertSame(-(1 << 50), Floats::tryConvertToInt((float)(-(1 << 50))));

        // PHP_INT_MIN is -2^63, which is a power of 2 and CAN be exactly represented as a float
        $this->assertSame(PHP_INT_MIN, Floats::tryConvertToInt((float)PHP_INT_MIN));

        // Note: PHP_INT_MAX (2^63 - 1) cannot be exactly represented as a float
        // because it has many bits set and exceeds the 53-bit mantissa precision
    }

    /**
     * Test tryConvertToInt with floats that lose precision when cast to int.
     */
    public function testTryConvertToIntOutOfRange(): void
    {
        // Float larger than PHP_INT_MAX (loses precision)
        $f = (float)PHP_INT_MAX * 2;
        // Verify it doesn't crash and returns int or null
        /** @var null|int $result */
        $result = Floats::tryConvertToInt($f);
        $this->assertTrue($result === null || is_int($result));
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
        $testCases = [
            0.1,
            0.5,
            0.999,
            1.1,
            -0.5,
            -1.5,
            3.14159,
            -2.71828,
        ];

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

    /**
     * Test rand returns finite floats.
     */
    public function testRandReturnsFiniteFloats(): void
    {
        // Generate multiple random floats and verify they're all finite
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::rand();
            $this->assertTrue(is_finite($f), 'Random float should be finite');
            $this->assertFalse(is_nan($f), 'Random float should not be NaN');
            $this->assertFalse(Floats::isSpecial($f), 'Random float should not be special');
        }
    }

    /**
     * Test rand returns different values.
     */
    public function testRandReturnsDifferentValues(): void
    {
        // Generate multiple random floats and verify they're not all the same
        $values = [];
        for ($i = 0; $i < 10; $i++) {
            $values[] = Floats::rand();
        }

        // Check that we got at least 2 different values (extremely unlikely to fail)
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Should generate different random values');
    }

    /**
     * Test randUniform with valid range.
     */
    public function testRandUniformWithValidRange(): void
    {
        $min = 10.0;
        $max = 20.0;

        // Generate multiple values and verify they're all in range
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::randUniform($min, $max);
            $this->assertGreaterThanOrEqual($min, $f, 'Value should be >= min');
            $this->assertLessThanOrEqual($max, $f, 'Value should be <= max');
            $this->assertTrue(is_finite($f), 'Value should be finite');
        }
    }

    /**
     * Test randUniform with negative range.
     */
    public function testRandUniformWithNegativeRange(): void
    {
        $min = -50.0;
        $max = -10.0;

        $f = Floats::randUniform($min, $max);
        $this->assertGreaterThanOrEqual($min, $f);
        $this->assertLessThanOrEqual($max, $f);
    }

    /**
     * Test randUniform with range crossing zero.
     */
    public function testRandUniformWithRangeCrossingZero(): void
    {
        $min = -10.0;
        $max = 10.0;

        $f = Floats::randUniform($min, $max);
        $this->assertGreaterThanOrEqual($min, $f);
        $this->assertLessThanOrEqual($max, $f);
    }

    /**
     * Test randUniform with min equal to max.
     */
    public function testRandUniformWithMinEqualToMax(): void
    {
        $value = 42.5;
        $f = Floats::randUniform($value, $value);
        $this->assertSame($value, $f);
    }

    /**
     * Test randUniform with min > max throws ValueError.
     */
    public function testRandUniformWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min must be less than or equal to max');
        Floats::randUniform(20.0, 10.0);
    }

    /**
     * Test randUniform with NaN min throws ValueError.
     */
    public function testRandUniformWithNanMinThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(NAN, 10.0);
    }

    /**
     * Test randUniform with NaN max throws ValueError.
     */
    public function testRandUniformWithNanMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(0.0, NAN);
    }

    /**
     * Test randUniform with positive infinity min throws ValueError.
     */
    public function testRandUniformWithInfMinThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(INF, 10.0);
    }

    /**
     * Test randUniform with negative infinity max throws ValueError.
     */
    public function testRandUniformWithNegativeInfMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(0.0, -INF);
    }

    /**
     * Test randUniform with negative zero accepts the value (treats as zero).
     */
    public function testRandUniformWithNegativeZeroMin(): void
    {
        // -0.0 is finite and compares equal to 0.0, so it's accepted
        $f = Floats::randUniform(-0.0, 10.0);
        $this->assertGreaterThanOrEqual(0.0, $f);
        $this->assertLessThanOrEqual(10.0, $f);
    }

    /**
     * Test randUniform with negative zero max accepts the value (treats as zero).
     */
    public function testRandUniformWithNegativeZeroMax(): void
    {
        // -0.0 compares equal to 0.0, so range [0.0, -0.0] is valid but degenerate
        $f = Floats::randUniform(0.0, -0.0);
        $this->assertSame(0.0, $f);
    }

    /**
     * Test next with regular positive numbers.
     */
    public function testNextWithPositiveNumbers(): void
    {
        $f = 1.0;
        $next = Floats::next($f);
        $this->assertGreaterThan($f, $next);
        $this->assertNotSame($f, $next);
    }

    /**
     * Test next with regular negative numbers.
     */
    public function testNextWithNegativeNumbers(): void
    {
        $f = -1.0;
        $next = Floats::next($f);
        $this->assertGreaterThan($f, $next);
        $this->assertLessThan(0.0, $next);
    }

    /**
     * Test next with positive zero.
     */
    public function testNextWithPositiveZero(): void
    {
        $f = 0.0;
        $next = Floats::next($f);
        $this->assertGreaterThan(0.0, $next);
        $this->assertTrue(Floats::isPositive($next));
    }

    /**
     * Test next with negative zero returns positive zero.
     */
    public function testNextWithNegativeZero(): void
    {
        $f = -0.0;
        $next = Floats::next($f);
        $this->assertSame(0.0, $next);
        $this->assertTrue(Floats::isPositiveZero($next));
    }

    /**
     * Test next with NaN returns NaN.
     */
    public function testNextWithNaN(): void
    {
        $next = Floats::next(NAN);
        $this->assertTrue(is_nan($next));
    }

    /**
     * Test next with PHP_FLOAT_MAX returns INF.
     */
    public function testNextWithMaxFloat(): void
    {
        $next = Floats::next(PHP_FLOAT_MAX);
        $this->assertSame(INF, $next);
    }

    /**
     * Test next with INF returns INF.
     */
    public function testNextWithInf(): void
    {
        $next = Floats::next(INF);
        $this->assertSame(INF, $next);
    }

    /**
     * Test next with -INF returns -PHP_FLOAT_MAX.
     */
    public function testNextWithNegativeInf(): void
    {
        $next = Floats::next(-INF);
        $this->assertSame(-PHP_FLOAT_MAX, $next);
    }

    /**
     * Test next with very small positive number.
     */
    public function testNextWithSmallPositiveNumber(): void
    {
        $f = 1e-100;
        $next = Floats::next($f);
        $this->assertGreaterThan($f, $next);
    }

    /**
     * Test previous with regular positive numbers.
     */
    public function testPreviousWithPositiveNumbers(): void
    {
        $f = 1.0;
        $prev = Floats::previous($f);
        $this->assertLessThan($f, $prev);
        $this->assertGreaterThan(0.0, $prev);
    }

    /**
     * Test previous with regular negative numbers.
     */
    public function testPreviousWithNegativeNumbers(): void
    {
        $f = -1.0;
        $prev = Floats::previous($f);
        $this->assertLessThan($f, $prev);
        $this->assertNotSame($f, $prev);
    }

    /**
     * Test previous with positive zero returns negative zero.
     */
    public function testPreviousWithPositiveZero(): void
    {
        $f = 0.0;
        $prev = Floats::previous($f);
        $this->assertSame(-0.0, $prev);
        $this->assertTrue(Floats::isNegativeZero($prev));
    }

    /**
     * Test previous with negative zero.
     */
    public function testPreviousWithNegativeZero(): void
    {
        $f = -0.0;
        $prev = Floats::previous($f);
        $this->assertLessThan(0.0, $prev);
        $this->assertTrue(Floats::isNegative($prev));
    }

    /**
     * Test previous with NaN returns NaN.
     */
    public function testPreviousWithNaN(): void
    {
        $prev = Floats::previous(NAN);
        $this->assertTrue(is_nan($prev));
    }

    /**
     * Test previous with -PHP_FLOAT_MAX returns -INF.
     */
    public function testPreviousWithMinFloat(): void
    {
        $prev = Floats::previous(-PHP_FLOAT_MAX);
        $this->assertSame(-INF, $prev);
    }

    /**
     * Test previous with -INF returns -INF.
     */
    public function testPreviousWithNegativeInf(): void
    {
        $prev = Floats::previous(-INF);
        $this->assertSame(-INF, $prev);
    }

    /**
     * Test previous with INF returns PHP_FLOAT_MAX.
     */
    public function testPreviousWithInf(): void
    {
        $prev = Floats::previous(INF);
        $this->assertSame(PHP_FLOAT_MAX, $prev);
    }

    /**
     * Test round-trip: next(previous(x)) should equal x for regular floats.
     */
    public function testNextPreviousRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 42.5, -99.9, 1e10, -1e-10];

        foreach ($testValues as $value) {
            $result = Floats::next(Floats::previous($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test round-trip: previous(next(x)) should equal x for regular floats.
     */
    public function testPreviousNextRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 42.5, -99.9, 1e10, -1e-10];

        foreach ($testValues as $value) {
            $result = Floats::previous(Floats::next($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test that next produces unique hex values.
     */
    public function testNextProducesUniqueHexValues(): void
    {
        $f = 1.0;
        $next = Floats::next($f);

        $hex1 = Floats::toHex($f);
        $hex2 = Floats::toHex($next);

        $this->assertNotSame($hex1, $hex2, 'next() should produce different binary representation');
    }

    /**
     * Test that previous produces unique hex values.
     */
    public function testPreviousProducesUniqueHexValues(): void
    {
        $f = 1.0;
        $prev = Floats::previous($f);

        $hex1 = Floats::toHex($f);
        $hex2 = Floats::toHex($prev);

        $this->assertNotSame($hex1, $hex2, 'previous() should produce different binary representation');
    }

    /**
     * Test next across zero boundary.
     */
    public function testNextAcrossZero(): void
    {
        // Start with negative zero
        $f = -0.0;
        $next = Floats::next($f);

        // Should get positive zero
        $this->assertSame(0.0, $next);
        $this->assertTrue(Floats::isPositiveZero($next));

        // Next from positive zero should be smallest positive number
        $next2 = Floats::next($next);
        $this->assertGreaterThan(0.0, $next2);
    }

    /**
     * Test previous across zero boundary.
     */
    public function testPreviousAcrossZero(): void
    {
        // Start with positive zero
        $f = 0.0;
        $prev = Floats::previous($f);

        // Should get negative zero
        $this->assertSame(-0.0, $prev);
        $this->assertTrue(Floats::isNegativeZero($prev));

        // Previous from negative zero should be smallest negative number
        $prev2 = Floats::previous($prev);
        $this->assertLessThan(0.0, $prev2);
    }

    // region disassemble tests

    /**
     * Test disassemble with positive one.
     */
    public function testDisassemblePositiveOne(): void
    {
        $result = Floats::disassemble(1.0);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(1023, $result['exponent']); // Bias is 1023, so 1.0 has exponent 0 + 1023
        $this->assertSame(0, $result['fraction']); // 1.0 has implicit 1, no fraction bits set
    }

    /**
     * Test disassemble with negative one.
     */
    public function testDisassembleNegativeOne(): void
    {
        $result = Floats::disassemble(-1.0);

        $this->assertSame(1, $result['sign']);
        $this->assertSame(1023, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with positive zero.
     */
    public function testDisassemblePositiveZero(): void
    {
        $result = Floats::disassemble(0.0);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(0, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with negative zero.
     */
    public function testDisassembleNegativeZero(): void
    {
        $result = Floats::disassemble(-0.0);

        $this->assertSame(1, $result['sign']);
        $this->assertSame(0, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with two (2^1).
     */
    public function testDisassembleTwo(): void
    {
        $result = Floats::disassemble(2.0);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(1024, $result['exponent']); // Exponent 1 + bias 1023
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with 1.5 (1 + 0.5).
     */
    public function testDisassembleOnePointFive(): void
    {
        $result = Floats::disassemble(1.5);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(1023, $result['exponent']);
        // 1.5 = 1.1 in binary, so fraction has MSB set
        $this->assertSame(1 << 51, $result['fraction']);
    }

    /**
     * Test disassemble with infinity.
     */
    public function testDisassembleInfinity(): void
    {
        $result = Floats::disassemble(INF);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(2047, $result['exponent']); // All 11 bits set
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with negative infinity.
     */
    public function testDisassembleNegativeInfinity(): void
    {
        $result = Floats::disassemble(-INF);

        $this->assertSame(1, $result['sign']);
        $this->assertSame(2047, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with NaN.
     */
    public function testDisassembleNaN(): void
    {
        $result = Floats::disassemble(NAN);

        // NaN has exponent all 1s and non-zero fraction
        $this->assertSame(2047, $result['exponent']);
        $this->assertGreaterThan(0, $result['fraction']);
    }

    // endregion

    // region assemble tests

    /**
     * Test assemble with positive one.
     */
    public function testAssemblePositiveOne(): void
    {
        $result = Floats::assemble(0, 1023, 0);
        $this->assertSame(1.0, $result);
    }

    /**
     * Test assemble with negative one.
     */
    public function testAssembleNegativeOne(): void
    {
        $result = Floats::assemble(1, 1023, 0);
        $this->assertSame(-1.0, $result);
    }

    /**
     * Test assemble with positive zero.
     */
    public function testAssemblePositiveZero(): void
    {
        $result = Floats::assemble(0, 0, 0);
        $this->assertSame(0.0, $result);
        $this->assertTrue(Floats::isPositiveZero($result));
    }

    /**
     * Test assemble with negative zero.
     */
    public function testAssembleNegativeZero(): void
    {
        $result = Floats::assemble(1, 0, 0);
        $this->assertSame(-0.0, $result);
        $this->assertTrue(Floats::isNegativeZero($result));
    }

    /**
     * Test assemble with two.
     */
    public function testAssembleTwo(): void
    {
        $result = Floats::assemble(0, 1024, 0);
        $this->assertSame(2.0, $result);
    }

    /**
     * Test assemble with 1.5.
     */
    public function testAssembleOnePointFive(): void
    {
        $result = Floats::assemble(0, 1023, 1 << 51);
        $this->assertSame(1.5, $result);
    }

    /**
     * Test assemble with infinity.
     */
    public function testAssembleInfinity(): void
    {
        $result = Floats::assemble(0, 2047, 0);
        $this->assertSame(INF, $result);
    }

    /**
     * Test assemble with negative infinity.
     */
    public function testAssembleNegativeInfinity(): void
    {
        $result = Floats::assemble(1, 2047, 0);
        $this->assertSame(-INF, $result);
    }

    /**
     * Test assemble with NaN (exponent 2047, non-zero fraction).
     */
    public function testAssembleNaN(): void
    {
        $result = Floats::assemble(0, 2047, 1);
        $this->assertTrue(is_nan($result));
    }

    /**
     * Test assemble round-trip with disassemble.
     */
    public function testAssembleDisassembleRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 2.0, 0.5, 1.5, -42.25, 1e10, 1e-10, PHP_FLOAT_MAX];

        foreach ($testValues as $value) {
            $parts = Floats::disassemble($value);
            $result = Floats::assemble($parts['sign'], $parts['exponent'], $parts['fraction']);
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test assemble with invalid sign throws ValueError.
     */
    public function testAssembleInvalidSignThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Sign must be 0 or 1');
        Floats::assemble(2, 1023, 0);
    }

    /**
     * Test assemble with negative sign throws ValueError.
     */
    public function testAssembleNegativeSignThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Sign must be 0 or 1');
        Floats::assemble(-1, 1023, 0);
    }

    /**
     * Test assemble with invalid exponent throws ValueError.
     */
    public function testAssembleInvalidExponentThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Exponent must be in the range [0, 2047]');
        Floats::assemble(0, 2048, 0);
    }

    /**
     * Test assemble with negative exponent throws ValueError.
     */
    public function testAssembleNegativeExponentThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Exponent must be in the range [0, 2047]');
        Floats::assemble(0, -1, 0);
    }

    /**
     * Test assemble with invalid fraction throws ValueError.
     */
    public function testAssembleInvalidFractionThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Fraction must be in the range');
        Floats::assemble(0, 1023, 0x10000000000000); // 2^52, one too large
    }

    /**
     * Test assemble with negative fraction throws ValueError.
     */
    public function testAssembleNegativeFractionThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Fraction must be in the range');
        Floats::assemble(0, 1023, -1);
    }

    // endregion

    // region rand with range tests

    /**
     * Test rand with valid positive range.
     */
    public function testRandWithRangeWithPositiveRange(): void
    {
        $min = 10.0;
        $max = 20.0;

        // Generate multiple values and verify they're all in range
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f, 'Value should be >= min');
            $this->assertLessThanOrEqual($max, $f, 'Value should be <= max');
            $this->assertTrue(is_finite($f), 'Value should be finite');
        }
    }

    /**
     * Test rand with negative range.
     */
    public function testRandWithRangeWithNegativeRange(): void
    {
        $min = -50.0;
        $max = -10.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with range crossing zero.
     */
    public function testRandWithRangeWithRangeCrossingZero(): void
    {
        $min = -10.0;
        $max = 10.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with narrow range (e.g., [0, 1]).
     */
    public function testRandWithRangeWithNarrowRange(): void
    {
        $min = 0.0;
        $max = 1.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with min equal to max returns that value.
     */
    public function testRandWithRangeWithMinEqualToMax(): void
    {
        $value = 42.5;
        $f = Floats::rand($value, $value);
        $this->assertSame($value, $f);
    }

    /**
     * Test rand with min > max throws ValueError.
     */
    public function testRandWithRangeWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min must be less than or equal to max');
        Floats::rand(20.0, 10.0);
    }

    /**
     * Test rand with NaN throws ValueError.
     */
    public function testRandWithRangeWithNanThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::rand(NAN, 10.0);
    }

    /**
     * Test rand with INF throws ValueError.
     */
    public function testRandWithRangeWithInfThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::rand(0.0, INF);
    }

    /**
     * Test rand returns different values (statistical test).
     */
    public function testRandWithRangeReturnsDifferentValues(): void
    {
        $min = 0.0;
        $max = 100.0;
        $values = [];

        for ($i = 0; $i < 20; $i++) {
            $values[] = Floats::rand($min, $max);
        }

        // Should have at least 2 different values
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Should generate different random values');
    }

    /**
     * Test rand with very small range.
     */
    public function testRandWithRangeWithVerySmallRange(): void
    {
        $min = 1.0;
        $max = 1.0 + 1e-10;

        for ($i = 0; $i < 20; $i++) {
            $f = Floats::rand($min, $max);
//            echo Stringify::stringifyFloat($f), PHP_EOL;
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with large range.
     */
    public function testRandWithRangeWithLargeRange(): void
    {
        $min = -1e100;
        $max = 1e100;

        for ($i = 0; $i < 20; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
            $this->assertTrue(is_finite($f));
        }
    }

    // endregion
}
