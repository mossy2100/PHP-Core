<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Floats;

use DomainException;
use OceanMoon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class - random number generation methods.
 */
#[CoversClass(Floats::class)]
final class FloatsRandomTest extends TestCase
{
    #region Random methods tests

    /**
     * Test rand returns finite floats.
     */
    public function testRandReturnsFiniteFloats(): void
    {
        // Generate multiple random floats and verify they're all finite
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::rand();
            $this->assertTrue(is_finite($f), 'Random float should be finite');
            $this->assertFalse(is_nan($f), 'Random float should not be NAN');
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
     * Test randUniform with min >= max throws DomainException.
     */
    public function testRandUniformWithMinEqualToMax(): void
    {
        $f = Floats::randUniform(42.5, 42.5);
        $this->assertEquals(42.5, $f);
    }

    /**
     * Test randUniform with min > max throws DomainException.
     */
    public function testRandUniformWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid range: [20.0, 10.0]. Minimum must not exceed maximum.');
        Floats::randUniform(20.0, 10.0);
    }

    /**
     * Test randUniform with NAN min throws DomainException.
     */
    public function testRandUniformWithNanMinThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid minimum: NAN. Must be finite.');
        Floats::randUniform(NAN, 10.0);
    }

    /**
     * Test randUniform with NAN max throws DomainException.
     */
    public function testRandUniformWithNanMaxThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid maximum: NAN. Must be finite.');
        Floats::randUniform(0.0, NAN);
    }

    /**
     * Test randUniform with positive infinity min throws DomainException.
     */
    public function testRandUniformWithInfMinThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid minimum: INF. Must be finite.');
        Floats::randUniform(INF, 10.0);
    }

    /**
     * Test randUniform with negative infinity max throws DomainException.
     */
    public function testRandUniformWithNegativeInfMaxThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid maximum: -INF. Must be finite.');
        Floats::randUniform(0.0, -INF);
    }

    /**
     * Test randUniform with negative zero as min or max normalizes to positive zero.
     */
    public function testRandUniformWithNegativeZero(): void
    {
        // -0.0 should be treated as 0.0, so this creates a valid range [0.0, 10.0]
        $f = Floats::randUniform(-0.0, 10.0);
        $this->assertGreaterThanOrEqual(0.0, $f);
        $this->assertLessThanOrEqual(10.0, $f);
    }

    /**
     * Test randUniform generates no duplicates.
     * This test creates a range of exactly 10 adjacent floats and samples it many times, to ensure an even distribution
     * across all possible results from the method.
     * With optimal step calculation, we should never get duplicates.
     */
    public function testRandUniformNoCollisions(): void
    {
        // Build a range of exactly 10 adjacent floats starting from 1.0
        $nValues = 10;
        $min = 1.0;
        $max = $min;
        $counts = [
            Floats::toHex($min) => 0,
        ];
        for ($i = 0; $i < $nValues - 1; $i++) {
            $f = Floats::next($max);
            $counts[Floats::toHex($f)] = 0;
            $max = $f;
        }

        // Sample the range many times, and count how many times each value appears.
        $nIters = 100000;
        for ($i = 0; $i < $nIters; $i++) {
            $f = Floats::randUniform($min, $max);
            $counts[Floats::toHex($f)]++;
        }

        // Check we got the right number of results.
        $this->assertEquals($nValues, count($counts));

        // Check we got a reasonably even distribution across the possible values.
        $avg = $nIters / $nValues;
        foreach ($counts as $count) {
            $this->assertGreaterThanOrEqual($avg * 0.9, $count);
            $this->assertLessThanOrEqual($avg * 1.1, $count);
        }
    }

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
     * Test rand with min > max throws DomainException.
     */
    public function testRandWithRangeWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid range: [20.0, 10.0]. Minimum must not exceed maximum.');
        Floats::rand(20.0, 10.0);
    }

    /**
     * Test rand with NAN throws DomainException.
     */
    public function testRandWithRangeWithNanThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid minimum: NAN. Must be finite.');
        Floats::rand(NAN, 10.0);
    }

    /**
     * Test rand with INF throws DomainException.
     */
    public function testRandWithRangeWithInfThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid maximum: INF. Must be finite.');
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

    #endregion
}
