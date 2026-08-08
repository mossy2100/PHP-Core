<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Floats;

use DomainException;
use OceanMoon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class - integer methods.
 */
#[CoversClass(Floats::class)]
final class FloatsIntegerTest extends TestCase
{
    #region Method toInt() tests.

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

    #region Method isInt() tests.

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

    #endregion

    #region Method isSafeInt() tests.

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

    #endregion

    #region Method isApproxInt() tests.

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
}
