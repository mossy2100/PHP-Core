<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Floats;

use DomainException;
use OceanMoon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class - comparison methods.
 */
#[CoversClass(Floats::class)]
final class FloatsComparisonTest extends TestCase
{
    #region Method approxEqual() tests.

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

    #region Method approxCompare() tests.

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
}
