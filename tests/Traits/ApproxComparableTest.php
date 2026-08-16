<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Traits;

use InvalidArgumentException;
use OceanMoon\Core\Floats;
use OceanMoon\Core\Traits\Comparison\ApproxComparable;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the ApproxComparable trait.
 */
#[CoversTrait(ApproxComparable::class)]
final class ApproxComparableTest extends TestCase
{
    #region Method approxCompare() tests.

    /**
     * Test approxCompare() returns 0 for values within tolerance, even if not exactly equal.
     */
    public function testApproxCompareReturnsZeroWithinTolerance(): void
    {
        $a = new ApproxComparableDummy(1.0);
        $b = new ApproxComparableDummy(1.0 + PHP_FLOAT_EPSILON);

        $this->assertSame(0, $a->approxCompare($b));
    }

    /**
     * Test approxCompare() falls back to compare() and returns -1 when this value is less, outside tolerance.
     */
    public function testApproxCompareReturnsNegativeOneOutsideTolerance(): void
    {
        $a = new ApproxComparableDummy(1.0);
        $b = new ApproxComparableDummy(2.0);

        $this->assertSame(-1, $a->approxCompare($b));
    }

    /**
     * Test approxCompare() falls back to compare() and returns 1 when this value is greater, outside tolerance.
     */
    public function testApproxCompareReturnsPositiveOneOutsideTolerance(): void
    {
        $a = new ApproxComparableDummy(2.0);
        $b = new ApproxComparableDummy(1.0);

        $this->assertSame(1, $a->approxCompare($b));
    }

    /**
     * Test approxCompare() respects custom tolerances.
     */
    public function testApproxCompareWithCustomTolerance(): void
    {
        $a = new ApproxComparableDummy(1000.0);
        $b = new ApproxComparableDummy(1001.0);

        $this->assertSame(-1, $a->approxCompare($b, relTol: 0.0001, absTol: 0.0));
        $this->assertSame(0, $a->approxCompare($b, relTol: 0.01, absTol: 0.0));
    }

    #endregion
}

/**
 * Minimal class using the ApproxComparable trait, for exercising it in isolation.
 */
final class ApproxComparableDummy
{
    use ApproxComparable;

    public function __construct(private readonly float $value)
    {
    }

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new InvalidArgumentException(
                'Cannot compare ApproxComparableDummy with ' . get_debug_type($other) . '.'
            );
        }

        return match (true) {
            $this->value < $other->value => -1,
            $this->value > $other->value => 1,
            default => 0,
        };
    }

    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        if (!$other instanceof self) {
            throw new InvalidArgumentException(
                'Cannot compare ApproxComparableDummy with ' . get_debug_type($other) . '.'
            );
        }

        return Floats::approxEqual($this->value, $other->value, $relTol, $absTol);
    }
}
