<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class.
 */
#[CoversClass(Floats::class)]
final class FloatsTest extends TestCase
{
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
        // Positive whole number
        $f = 5.0;
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(5, $i);

        // Negative whole number
        $f = -10.0;
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(-10, $i);

        // Zero
        $f = 0.0;
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(0, $i);

        // Large whole number
        $f = 1000000.0;
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(1000000, $i);
    }

    /**
     * Test tryConvertToInt with floats that have fractional parts.
     */
    public function testTryConvertToIntWithFractionalNumbers(): void
    {
        // Float with fractional part
        $f = 5.5;
        $i = null;
        $this->assertFalse(Floats::tryConvertToInt($f, $i));
        $this->assertNull($i);

        // Small fractional part
        $f = 1.001;
        $i = null;
        $this->assertFalse(Floats::tryConvertToInt($f, $i));
        $this->assertNull($i);

        // Negative with fractional part
        $f = -3.14;
        $i = null;
        $this->assertFalse(Floats::tryConvertToInt($f, $i));
        $this->assertNull($i);
    }

    /**
     * Test tryConvertToInt with edge case floats.
     */
    public function testTryConvertToIntEdgeCases(): void
    {
        // Very small positive number (not zero)
        $f = 0.1;
        $i = null;
        $this->assertFalse(Floats::tryConvertToInt($f, $i));
        $this->assertNull($i);

        // Very small negative number (not zero)
        $f = -0.1;
        $i = null;
        $this->assertFalse(Floats::tryConvertToInt($f, $i));
        $this->assertNull($i);

        // Negative zero
        $f = -0.0;
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(0, $i);
    }

    /**
     * Test tryConvertToInt with large integers that can be exactly represented as floats.
     */
    public function testTryConvertToIntWithLargeIntegers(): void
    {
        // Use powers of 2 up to 2^53, which can be exactly represented as floats
        $f = (float)(1 << 50); // 2^50 - well within exact float range
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(1 << 50, $i);

        // Negative large integer
        $f = (float)(-(1 << 50));
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(-(1 << 50), $i);

        // PHP_INT_MIN is -2^63, which is a power of 2 and CAN be exactly represented as a float
        $f = (float)PHP_INT_MIN;
        $this->assertTrue(Floats::tryConvertToInt($f, $i));
        $this->assertSame(PHP_INT_MIN, $i);

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
        // This may or may not convert depending on platform precision
        // Just verify it doesn't crash
        $result = Floats::tryConvertToInt($f, $i);
        $this->assertIsBool($result); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * Test tryConvertToInt doesn't modify output parameter on failure.
     */
    public function testTryConvertToIntDoesNotModifyOnFailure(): void
    {
        $f = 3.14;

        $this->assertFalse(Floats::tryConvertToInt($f, $i));
        // Output parameter should not be modified on failure
        $this->assertNull($i);
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
            $this->assertTrue(Floats::tryConvertToInt($float, $i), "Failed for $float");
            $this->assertSame($expectedInt, $i, "Wrong conversion for $float");
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
            $i = null;
            $this->assertFalse(Floats::tryConvertToInt($float, $i), "Should fail for $float");
            $this->assertNull($i, "Should not modify output for $float");
        }
    }
}
