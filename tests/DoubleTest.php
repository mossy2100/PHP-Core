<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Double;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Double utility class.
 */
#[CoversClass(Double::class)]
final class DoubleTest extends TestCase
{
    /**
     * Test detection of negative zero.
     */
    public function testIsNegativeZero(): void
    {
        // Test that -0.0 is correctly identified as negative zero.
        $this->assertTrue(Double::isNegativeZero(-0.0));

        // Test that positive zero is not negative zero.
        $this->assertFalse(Double::isNegativeZero(0.0));

        // Test that positive values are not negative zero.
        $this->assertFalse(Double::isNegativeZero(1.0));

        // Test that negative values are not negative zero.
        $this->assertFalse(Double::isNegativeZero(-1.0));

        // Test that infinity values are not negative zero.
        $this->assertFalse(Double::isNegativeZero(INF));
        $this->assertFalse(Double::isNegativeZero(-INF));

        // Test that NaN is not negative zero.
        $this->assertFalse(Double::isNegativeZero(NAN));
    }

    /**
     * Test detection of positive zero.
     */
    public function testIsPositiveZero(): void
    {
        // Test that +0.0 is correctly identified as positive zero.
        $this->assertTrue(Double::isPositiveZero(0.0));

        // Test that negative zero is not positive zero.
        $this->assertFalse(Double::isPositiveZero(-0.0));

        // Test that positive values are not positive zero.
        $this->assertFalse(Double::isPositiveZero(1.0));

        // Test that negative values are not positive zero.
        $this->assertFalse(Double::isPositiveZero(-1.0));

        // Test that infinity values are not positive zero.
        $this->assertFalse(Double::isPositiveZero(INF));
        $this->assertFalse(Double::isPositiveZero(-INF));

        // Test that NaN is not positive zero.
        $this->assertFalse(Double::isPositiveZero(NAN));
    }

    /**
     * Test normalization of zero values.
     */
    public function testNormalizeZero(): void
    {
        // Test that negative zero is normalized to positive zero.
        $this->assertSame(0.0, Double::normalizeZero(-0.0));

        // Test that positive zero remains positive zero.
        $this->assertSame(0.0, Double::normalizeZero(0.0));

        // Test that positive values are unchanged.
        $this->assertSame(1.5, Double::normalizeZero(1.5));

        // Test that negative values are unchanged.
        $this->assertSame(-2.5, Double::normalizeZero(-2.5));

        // Test that infinity values are unchanged.
        $this->assertSame(INF, Double::normalizeZero(INF));
        $this->assertSame(-INF, Double::normalizeZero(-INF));

        // Test that NaN is unchanged (NaN !== NaN, so use is_nan).
        $this->assertTrue(is_nan(Double::normalizeZero(NAN)));
    }

    /**
     * Test detection of negative values.
     */
    public function testIsNegative(): void
    {
        // Test that negative values are correctly identified.
        $this->assertTrue(Double::isNegative(-1.0));
        $this->assertTrue(Double::isNegative(-0.5));
        $this->assertTrue(Double::isNegative(-100.0));

        // Test that negative zero is identified as negative.
        $this->assertTrue(Double::isNegative(-0.0));

        // Test that negative infinity is identified as negative.
        $this->assertTrue(Double::isNegative(-INF));

        // Test that positive values are not negative.
        $this->assertFalse(Double::isNegative(1.0));
        $this->assertFalse(Double::isNegative(0.5));

        // Test that positive zero is not negative.
        $this->assertFalse(Double::isNegative(0.0));

        // Test that positive infinity is not negative.
        $this->assertFalse(Double::isNegative(INF));

        // Test that NaN is not negative.
        $this->assertFalse(Double::isNegative(NAN));
    }

    /**
     * Test detection of positive values.
     */
    public function testIsPositive(): void
    {
        // Test that positive values are correctly identified.
        $this->assertTrue(Double::isPositive(1.0));
        $this->assertTrue(Double::isPositive(0.5));
        $this->assertTrue(Double::isPositive(100.0));

        // Test that positive zero is identified as positive.
        $this->assertTrue(Double::isPositive(0.0));

        // Test that positive infinity is identified as positive.
        $this->assertTrue(Double::isPositive(INF));

        // Test that negative values are not positive.
        $this->assertFalse(Double::isPositive(-1.0));
        $this->assertFalse(Double::isPositive(-0.5));

        // Test that negative zero is not positive.
        $this->assertFalse(Double::isPositive(-0.0));

        // Test that negative infinity is not positive.
        $this->assertFalse(Double::isPositive(-INF));

        // Test that NaN is not positive.
        $this->assertFalse(Double::isPositive(NAN));
    }

    /**
     * Test detection of special float values.
     */
    public function testIsSpecial(): void
    {
        // Test that NaN is identified as special.
        $this->assertTrue(Double::isSpecial(NAN));

        // Test that negative zero is identified as special.
        $this->assertTrue(Double::isSpecial(-0.0));

        // Test that positive infinity is identified as special.
        $this->assertTrue(Double::isSpecial(INF));

        // Test that negative infinity is identified as special.
        $this->assertTrue(Double::isSpecial(-INF));

        // Test that positive zero is not special.
        $this->assertFalse(Double::isSpecial(0.0));

        // Test that regular positive values are not special.
        $this->assertFalse(Double::isSpecial(1.0));
        $this->assertFalse(Double::isSpecial(42.5));

        // Test that regular negative values are not special.
        $this->assertFalse(Double::isSpecial(-1.0));
        $this->assertFalse(Double::isSpecial(-42.5));
    }

    /**
     * Test conversion of floats to hexadecimal strings.
     */
    public function testToHex(): void
    {
        // Test that positive zero produces a consistent hex string.
        $hexZero = Double::toHex(0.0);
        $this->assertSame(16, strlen($hexZero));

        // Test that negative zero produces a different hex string than positive zero.
        $hexNegZero = Double::toHex(-0.0);
        $this->assertSame(16, strlen($hexNegZero));
        $this->assertNotSame($hexZero, $hexNegZero);

        // Test that a regular value produces a 16-character hex string.
        $hex1 = Double::toHex(1.0);
        $this->assertSame(16, strlen($hex1));

        // Test that different values produce different hex strings.
        $hex2 = Double::toHex(2.0);
        $this->assertNotSame($hex1, $hex2);

        // Test that special values produce valid hex strings.
        $this->assertSame(16, strlen(Double::toHex(INF)));
        $this->assertSame(16, strlen(Double::toHex(-INF)));
        $this->assertSame(16, strlen(Double::toHex(NAN)));

        // Test that very close but different values produce different hex strings.
        $this->assertNotSame(Double::toHex(1.0), Double::toHex(1.0 + PHP_FLOAT_EPSILON));
    }

    /**
     * Test wrapping of angles in unsigned (positive) range.
     */
    public function testWrapUnsigned(): void
    {
        // Test wrapping with degrees (360 units per turn).
        // Value already in range [0, 360).
        $this->assertEqualsWithDelta(45.0, Double::wrap(45.0, 360.0), 1e-10);

        // Value at lower bound should remain unchanged.
        $this->assertEqualsWithDelta(0.0, Double::wrap(0.0, 360.0), 1e-10);

        // Value just below upper bound should remain unchanged.
        $this->assertEqualsWithDelta(359.9, Double::wrap(359.9, 360.0), 1e-10);

        // Value equal to upper bound should wrap to 0.
        $this->assertEqualsWithDelta(0.0, Double::wrap(360.0, 360.0), 1e-10);

        // Value above range should wrap into [0, 360).
        $this->assertEqualsWithDelta(45.0, Double::wrap(405.0, 360.0), 1e-10);

        // Negative value should wrap into [0, 360).
        $this->assertEqualsWithDelta(315.0, Double::wrap(-45.0, 360.0), 1e-10);

        // Large positive value should wrap correctly.
        $this->assertEqualsWithDelta(90.0, Double::wrap(810.0, 360.0), 1e-10);

        // Large negative value should wrap correctly.
        $this->assertEqualsWithDelta(270.0, Double::wrap(-450.0, 360.0), 1e-10);
    }

    /**
     * Test wrapping of angles in signed (centered) range.
     */
    public function testWrapSigned(): void
    {
        // Test wrapping with degrees in signed range [-180, 180).
        // Value in range should remain unchanged.
        $this->assertEqualsWithDelta(45.0, Double::wrap(45.0, 360.0, true), 1e-10);

        // Value at lower bound should remain unchanged.
        $this->assertEqualsWithDelta(-180.0, Double::wrap(-180.0, 360.0, true), 1e-10);

        // Value just below upper bound should remain unchanged.
        $this->assertEqualsWithDelta(179.9, Double::wrap(179.9, 360.0, true), 1e-10);

        // Value equal to upper bound should wrap to lower bound.
        $this->assertEqualsWithDelta(-180.0, Double::wrap(180.0, 360.0, true), 1e-10);

        // Value above range should wrap into [-180, 180).
        $this->assertEqualsWithDelta(-135.0, Double::wrap(225.0, 360.0, true), 1e-10);

        // Large positive value should wrap correctly.
        $this->assertEqualsWithDelta(90.0, Double::wrap(450.0, 360.0, true), 1e-10);

        // Large negative value should wrap correctly.
        $this->assertEqualsWithDelta(-90.0, Double::wrap(-450.0, 360.0, true), 1e-10);

        // Zero should remain zero.
        $this->assertEqualsWithDelta(0.0, Double::wrap(0.0, 360.0, true), 1e-10);
    }

    /**
     * Test wrapping with radians (TAU units per turn).
     */
    public function testWrapRadians(): void
    {
        $tau = 2 * M_PI;

        // Test unsigned range [0, TAU).
        $this->assertEqualsWithDelta(M_PI / 4, Double::wrap(M_PI / 4, $tau), 1e-10);
        $this->assertEqualsWithDelta(M_PI / 4, Double::wrap($tau + M_PI / 4, $tau), 1e-10);
        $this->assertEqualsWithDelta(7 * M_PI / 4, Double::wrap(-M_PI / 4, $tau), 1e-10);

        // Test signed range [-PI, PI).
        $this->assertEqualsWithDelta(M_PI / 4, Double::wrap(M_PI / 4, $tau, true), 1e-10);
        $this->assertEqualsWithDelta(-3 * M_PI / 4, Double::wrap(5 * M_PI / 4, $tau, true), 1e-10);
        $this->assertEqualsWithDelta(-M_PI / 4, Double::wrap(-M_PI / 4, $tau, true), 1e-10);
    }

    /**
     * Test wrapping with gradians (400 units per turn).
     */
    public function testWrapGradians(): void
    {
        // Test unsigned range [0, 400).
        $this->assertEqualsWithDelta(50.0, Double::wrap(50.0, 400.0), 1e-10);
        $this->assertEqualsWithDelta(50.0, Double::wrap(450.0, 400.0), 1e-10);
        $this->assertEqualsWithDelta(350.0, Double::wrap(-50.0, 400.0), 1e-10);

        // Test signed range [-200, 200).
        $this->assertEqualsWithDelta(50.0, Double::wrap(50.0, 400.0, true), 1e-10);
        $this->assertEqualsWithDelta(-150.0, Double::wrap(250.0, 400.0, true), 1e-10);
        $this->assertEqualsWithDelta(-50.0, Double::wrap(-50.0, 400.0, true), 1e-10);
    }

    /**
     * Test that wrapping normalizes negative zero to positive zero.
     */
    public function testWrapNormalizesNegativeZero(): void
    {
        // When wrapping produces -0.0, it should be normalized to 0.0.
        $result = Double::wrap(0.0, 360.0);
        $this->assertSame(0.0, $result);
        $this->assertFalse(Double::isNegativeZero($result));

        $result = Double::wrap(360.0, 360.0);
        $this->assertSame(0.0, $result);
        $this->assertFalse(Double::isNegativeZero($result));
    }
}
