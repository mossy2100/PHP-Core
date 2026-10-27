<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Floats;

use DomainException;
use Mossy2100\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class - random number generation methods.
 */
#[CoversClass(Floats::class)]
final class FloatsRandomTest extends TestCase
{
    #region Method randRepresentable() tests.

    /**
     * Test randRepresentable returns finite floats.
     */
    public function testRandRepresentableReturnsFiniteFloats(): void
    {
        // Generate multiple random floats and verify they're all finite
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::randRepresentable();
            $this->assertTrue(is_finite($f), 'Random float should be finite');
            $this->assertFalse(is_nan($f), 'Random float should not be NAN');
            $this->assertFalse(Floats::isSpecial($f), 'Random float should not be special');
        }
    }

    /**
     * Test randRepresentable returns different values.
     */
    public function testRandRepresentableReturnsDifferentValues(): void
    {
        // Generate multiple random floats and verify they're not all the same
        $values = [];
        for ($i = 0; $i < 10; $i++) {
            $values[] = Floats::randRepresentable();
        }

        // Check that we got at least 2 different values (extremely unlikely to fail)
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Should generate different random values');
    }

    /**
     * Test randRepresentable with valid positive range.
     */
    public function testRandRepresentableWithRangeWithPositiveRange(): void
    {
        $min = 10.0;
        $max = 20.0;

        // Generate multiple values and verify they're all in range
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f, 'Value should be >= min');
            $this->assertLessThanOrEqual($max, $f, 'Value should be <= max');
            $this->assertTrue(is_finite($f), 'Value should be finite');
        }
    }

    /**
     * Test randRepresentable with negative range.
     */
    public function testRandRepresentableWithRangeWithNegativeRange(): void
    {
        $min = -50.0;
        $max = -10.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test randRepresentable with range crossing zero.
     */
    public function testRandRepresentableWithRangeWithRangeCrossingZero(): void
    {
        $min = -10.0;
        $max = 10.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test randRepresentable with narrow range (e.g., [0, 1]).
     */
    public function testRandRepresentableWithRangeWithNarrowRange(): void
    {
        $min = 0.0;
        $max = 1.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test randRepresentable with narrow ranges across a power of two or zero, which used to never return.
     *
     * @param float $min The minimum value.
     * @param float $max The maximum value.
     * @param list<float> $expected Values that should all appear in 200 draws.
     */
    #[DataProvider('rangeAcrossBinadesProvider')]
    public function testRandRepresentableWithRangeAcrossBinades(float $min, float $max, array $expected): void
    {
        $seen = [];
        for ($i = 0; $i < 200; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
            $this->assertFalse(Floats::isNegativeZero($f), 'Should never return -0.0');
            $seen[] = $f;
        }

        foreach ($expected as $value) {
            $this->assertContains($value, $seen);
        }
    }

    /**
     * Narrow ranges whose bounds have different exponents or signs.
     *
     * @return array<string, array{float, float, list<float>}>
     */
    public static function rangeAcrossBinadesProvider(): array
    {
        $tiny = Floats::nextUp(0.0);
        return [
            'just below 1 to 1' => [Floats::nextDown(1.0), 1.0, [Floats::nextDown(1.0), 1.0]],
            'around 2' => [1.9999999, 2.0000001, []],
            'smallest subnormals around zero' => [-$tiny, $tiny, [-$tiny, 0.0, $tiny]],
            'negative to zero' => [-1.0, 0.0, []],
        ];
    }

    /**
     * Test randRepresentable gives every representable float the same chance, so a bound at a power of two, alone in
     * its binade, is no more likely than any other value.
     */
    public function testRandRepresentableWithPowerOfTwoBoundIsRare(): void
    {
        for ($i = 0; $i < 1000; $i++) {
            $this->assertNotSame(4.0, Floats::randRepresentable(1.0, 4.0));
        }
    }

    /**
     * Test randRepresentable with min equal to max returns that value.
     */
    public function testRandRepresentableWithRangeWithMinEqualToMax(): void
    {
        $value = 42.5;
        $f = Floats::randRepresentable($value, $value);
        $this->assertSame($value, $f);
    }

    /**
     * Test randRepresentable with min > max throws DomainException.
     */
    public function testRandRepresentableWithRangeWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid range: [20.0, 10.0]. Minimum must not exceed maximum.');
        Floats::randRepresentable(20.0, 10.0);
    }

    /**
     * Test randRepresentable with NAN throws DomainException.
     */
    public function testRandRepresentableWithRangeWithNanThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid minimum: NAN. Must be finite.');
        Floats::randRepresentable(NAN, 10.0);
    }

    /**
     * Test randRepresentable with INF throws DomainException.
     */
    public function testRandRepresentableWithRangeWithInfThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid maximum: INF. Must be finite.');
        Floats::randRepresentable(0.0, INF);
    }

    /**
     * Test randRepresentable returns different values (statistical test).
     */
    public function testRandRepresentableWithRangeReturnsDifferentValues(): void
    {
        $min = 0.0;
        $max = 100.0;
        $values = [];

        for ($i = 0; $i < 20; $i++) {
            $values[] = Floats::randRepresentable($min, $max);
        }

        // Should have at least 2 different values
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Should generate different random values');
    }

    /**
     * Test randRepresentable with very small range.
     */
    public function testRandRepresentableWithRangeWithVerySmallRange(): void
    {
        $min = 1.0;
        $max = 1.0 + 1e-10;

        for ($i = 0; $i < 20; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test randRepresentable with large range.
     */
    public function testRandRepresentableWithRangeWithLargeRange(): void
    {
        $min = -1e100;
        $max = 1e100;

        for ($i = 0; $i < 20; $i++) {
            $f = Floats::randRepresentable($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
            $this->assertTrue(is_finite($f));
        }
    }

    #endregion

    #region Method randUniform() tests.

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
        $counts = [Floats::toHex($min) => 0];
        for ($i = 0; $i < $nValues - 1; $i++) {
            $f = Floats::nextUp($max);
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
     * Test randUniform with ranges wide enough that max - min, or the ULP of the bounds, used to overflow to INF.
     *
     * @param float $min The minimum value.
     * @param float $max The maximum value.
     */
    #[DataProvider('wideRangeProvider')]
    public function testRandUniformWithWideRange(float $min, float $max): void
    {
        $belowMidpoint = 0;
        $midpoint = $min / 2 + $max / 2;
        for ($i = 0; $i < 1000; $i++) {
            $f = Floats::randUniform($min, $max);
            $this->assertTrue(is_finite($f), 'Value should be finite');
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
            if ($f < $midpoint) {
                $belowMidpoint++;
            }
        }

        // Values should land on both sides of the midpoint, roughly evenly.
        $this->assertGreaterThan(400, $belowMidpoint);
        $this->assertLessThan(600, $belowMidpoint);
    }

    /**
     * Ranges that overflowed before.
     *
     * @return array<string, array{float, float}>
     */
    public static function wideRangeProvider(): array
    {
        return [
            'full float range' => [-PHP_FLOAT_MAX, PHP_FLOAT_MAX],
            'zero to PHP_FLOAT_MAX' => [0.0, PHP_FLOAT_MAX],
            'symmetric, wider than max' => [-1e308, 1e308],
        ];
    }

    #endregion
}
