<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Floats;

use DomainException;
use Mossy2100\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Floats utility class - adjacent floats (next/previous) and bit-manipulation methods.
 */
#[CoversClass(Floats::class)]
final class FloatsBitOperationsTest extends TestCase
{
    #region Method nextUp() tests.

    /**
     * Test nextUp with regular positive numbers.
     */
    public function testNextUpWithPositiveNumbers(): void
    {
        $f = 1.0;
        $next = Floats::nextUp($f);
        $this->assertGreaterThan($f, $next);
        $this->assertNotSame($f, $next);
    }

    /**
     * Test nextUp with regular negative numbers.
     */
    public function testNextUpWithNegativeNumbers(): void
    {
        $f = -1.0;
        $next = Floats::nextUp($f);
        $this->assertGreaterThan($f, $next);
        $this->assertLessThan(0.0, $next);
    }

    /**
     * Test nextUp with positive zero.
     */
    public function testNextUpWithPositiveZero(): void
    {
        $f = 0.0;
        $next = Floats::nextUp($f);
        $this->assertGreaterThan(0.0, $next);
        $this->assertTrue(Floats::isPositive($next));
    }

    /**
     * Test nextUp with negative zero returns the smallest positive subnormal, as for positive zero.
     */
    public function testNextUpWithNegativeZero(): void
    {
        $this->assertSame(5e-324, Floats::nextUp(-0.0));
    }

    /**
     * Test nextUp with NAN returns NAN.
     */
    public function testNextUpWithNan(): void
    {
        $next = Floats::nextUp(NAN);
        $this->assertTrue(is_nan($next));
    }

    /**
     * Test nextUp with PHP_FLOAT_MAX returns INF.
     */
    public function testNextUpWithMaxFloat(): void
    {
        $next = Floats::nextUp(PHP_FLOAT_MAX);
        $this->assertSame(INF, $next);
    }

    /**
     * Test nextUp with INF returns INF.
     */
    public function testNextUpWithInf(): void
    {
        $next = Floats::nextUp(INF);
        $this->assertSame(INF, $next);
    }

    /**
     * Test nextUp with -INF returns -PHP_FLOAT_MAX.
     */
    public function testNextUpWithNegativeInf(): void
    {
        $next = Floats::nextUp(-INF);
        $this->assertSame(-PHP_FLOAT_MAX, $next);
    }

    /**
     * Test nextUp with very small positive number.
     */
    public function testNextUpWithSmallPositiveNumber(): void
    {
        $f = 1e-100;
        $next = Floats::nextUp($f);
        $this->assertGreaterThan($f, $next);
    }

    #endregion

    #region Method nextDown() tests.

    /**
     * Test nextDown with regular positive numbers.
     */
    public function testNextDownWithPositiveNumbers(): void
    {
        $f = 1.0;
        $prev = Floats::nextDown($f);
        $this->assertLessThan($f, $prev);
        $this->assertGreaterThan(0.0, $prev);
    }

    /**
     * Test nextDown with regular negative numbers.
     */
    public function testNextDownWithNegativeNumbers(): void
    {
        $f = -1.0;
        $prev = Floats::nextDown($f);
        $this->assertLessThan($f, $prev);
        $this->assertNotSame($f, $prev);
    }

    /**
     * Test nextDown with positive zero returns the largest negative subnormal, as for negative zero.
     */
    public function testNextDownWithPositiveZero(): void
    {
        $this->assertSame(-5e-324, Floats::nextDown(0.0));
    }

    /**
     * Test nextDown with negative zero.
     */
    public function testNextDownWithNegativeZero(): void
    {
        $f = -0.0;
        $prev = Floats::nextDown($f);
        $this->assertLessThan(0.0, $prev);
        $this->assertTrue(Floats::isNegative($prev));
    }

    /**
     * Test nextDown with NAN returns NAN.
     */
    public function testNextDownWithNan(): void
    {
        $prev = Floats::nextDown(NAN);
        $this->assertTrue(is_nan($prev));
    }

    /**
     * Test nextDown with -PHP_FLOAT_MAX returns -INF.
     */
    public function testNextDownWithMinFloat(): void
    {
        $prev = Floats::nextDown(-PHP_FLOAT_MAX);
        $this->assertSame(-INF, $prev);
    }

    /**
     * Test nextDown with -INF returns -INF.
     */
    public function testNextDownWithNegativeInf(): void
    {
        $prev = Floats::nextDown(-INF);
        $this->assertSame(-INF, $prev);
    }

    /**
     * Test nextDown with INF returns PHP_FLOAT_MAX.
     */
    public function testNextDownWithInf(): void
    {
        $prev = Floats::nextDown(INF);
        $this->assertSame(PHP_FLOAT_MAX, $prev);
    }

    #endregion

    #region nextUp()/nextDown() round-trip and consistency tests.

    /**
     * Test round-trip: next(previous(x)) should equal x for regular floats.
     */
    public function testNextUpNextDownRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 42.5, -99.9, 1e10, -1e-10];

        foreach ($testValues as $value) {
            $result = Floats::nextUp(Floats::nextDown($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test round-trip: previous(next(x)) should equal x for regular floats.
     */
    public function testNextDownNextUpRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 42.5, -99.9, 1e10, -1e-10];

        foreach ($testValues as $value) {
            $result = Floats::nextDown(Floats::nextUp($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test that next produces unique hex values.
     */
    public function testNextUpProducesUniqueHexValues(): void
    {
        $f = 1.0;
        $next = Floats::nextUp($f);

        $hex1 = Floats::toHex($f);
        $hex2 = Floats::toHex($next);

        $this->assertNotSame($hex1, $hex2, 'nextUp() should produce different binary representation');
    }

    /**
     * Test that previous produces unique hex values.
     */
    public function testNextDownProducesUniqueHexValues(): void
    {
        $f = 1.0;
        $prev = Floats::nextDown($f);

        $hex1 = Floats::toHex($f);
        $hex2 = Floats::toHex($prev);

        $this->assertNotSame($hex1, $hex2, 'nextDown() should produce different binary representation');
    }

    /**
     * Test nextUp across zero boundary.
     */
    public function testNextUpAcrossZero(): void
    {
        // The largest negative subnormal steps to -0.0, which then steps straight to the smallest positive
        // subnormal, as IEEE 754 requires: -0.0 and 0.0 are equal, so stepping between them isn't a step up.
        $zero = Floats::nextUp(-5e-324);
        $this->assertTrue(Floats::isNegativeZero($zero));
        $this->assertSame(5e-324, Floats::nextUp($zero));

        // nextUp() always returns a greater value.
        foreach ([-5e-324, -0.0, 0.0, 5e-324] as $f) {
            $this->assertGreaterThan($f, Floats::nextUp($f));
        }
    }

    /**
     * Test nextDown across zero boundary.
     */
    public function testNextDownAcrossZero(): void
    {
        // The smallest positive subnormal steps to 0.0, which then steps straight to the largest negative subnormal.
        $zero = Floats::nextDown(5e-324);
        $this->assertTrue(Floats::isPositiveZero($zero));
        $this->assertSame(-5e-324, Floats::nextDown($zero));

        // nextDown() always returns a lesser value.
        foreach ([5e-324, 0.0, -0.0, -5e-324] as $f) {
            $this->assertLessThan($f, Floats::nextDown($f));
        }
    }

    #endregion

    #region Method floatToBits() tests.

    /**
     * Test floatToBits with positive zero.
     */
    public function testFloatToBitsPositiveZero(): void
    {
        $this->assertSame(0, Floats::floatToBits(0.0));
    }

    /**
     * Test floatToBits with negative zero.
     */
    public function testFloatToBitsNegativeZero(): void
    {
        // Sign bit set, everything else zero (0x8000000000000000 as signed int).
        $this->assertSame(PHP_INT_MIN, Floats::floatToBits(-0.0));
    }

    /**
     * Test floatToBits with positive one.
     */
    public function testFloatToBitsPositiveOne(): void
    {
        // 1.0 = sign 0, exponent 1023 (0x3FF), fraction 0.
        $this->assertSame(4607182418800017408, Floats::floatToBits(1.0));
    }

    /**
     * Test floatToBits with negative one.
     */
    public function testFloatToBitsNegativeOne(): void
    {
        // -1.0 = 0xBFF0000000000000 as signed int.
        $this->assertSame(-4616189618054758400, Floats::floatToBits(-1.0));
    }

    /**
     * Test floatToBits with positive infinity.
     */
    public function testFloatToBitsInfinity(): void
    {
        // INF = sign 0, exponent 2047 (0x7FF), fraction 0.
        $this->assertSame(9218868437227405312, Floats::floatToBits(INF));
    }

    /**
     * Test floatToBits with negative infinity.
     */
    public function testFloatToBitsNegativeInfinity(): void
    {
        // -INF = 0xFFF0000000000000 as signed int.
        $this->assertSame(-4503599627370496, Floats::floatToBits(-INF));
    }

    /**
     * Test floatToBits with NAN has exponent all ones and non-zero fraction.
     */
    public function testFloatToBitsNan(): void
    {
        $bits = Floats::floatToBits(NAN);

        // Disassemble to check components. Exponent should be 2047 (all ones), fraction non-zero.
        $parts = Floats::disassemble(NAN);
        $this->assertSame(2047, $parts['exponent']);
        $this->assertGreaterThan(0, $parts['fraction']);

        // Verify floatToBits is consistent with disassemble.
        $this->assertSame($bits, Floats::floatToBits(Floats::bitsToFloat($bits)));
    }

    /**
     * Test floatToBits with 1.5.
     */
    public function testFloatToBitsOnePointFive(): void
    {
        // 1.5 = 1.1 in binary = sign 0, exponent 1023, fraction MSB set.
        $this->assertSame(4609434218613702656, Floats::floatToBits(1.5));
    }

    #endregion

    #region Method bitsToFloat() tests.

    /**
     * Test bitsToFloat with zero bits returns positive zero.
     */
    public function testBitsToFloatZero(): void
    {
        $result = Floats::bitsToFloat(0);
        $this->assertSame(0.0, $result);
        $this->assertTrue(Floats::isPositiveZero($result));
    }

    /**
     * Test bitsToFloat with sign bit set returns negative zero.
     */
    public function testBitsToFloatNegativeZero(): void
    {
        $result = Floats::bitsToFloat(PHP_INT_MIN);
        $this->assertTrue(Floats::isNegativeZero($result));
    }

    /**
     * Test bitsToFloat with known bit patterns.
     */
    public function testBitsToFloatKnownValues(): void
    {
        $this->assertSame(1.0, Floats::bitsToFloat(4607182418800017408));
        $this->assertSame(-1.0, Floats::bitsToFloat(-4616189618054758400));
        $this->assertSame(2.0, Floats::bitsToFloat(4611686018427387904));
        $this->assertSame(1.5, Floats::bitsToFloat(4609434218613702656));
        $this->assertSame(INF, Floats::bitsToFloat(9218868437227405312));
        $this->assertSame(-INF, Floats::bitsToFloat(-4503599627370496));
    }

    /**
     * Test bitsToFloat with NAN bit pattern.
     */
    public function testBitsToFloatNan(): void
    {
        // Use a known NAN bit pattern from floatToBits.
        $nanBits = Floats::floatToBits(NAN);
        $this->assertTrue(is_nan(Floats::bitsToFloat($nanBits)));
    }

    #endregion

    #region floatToBits()/bitsToFloat() round-trip tests.

    /**
     * Test floatToBits and bitsToFloat round-trip.
     */
    public function testFloatToBitsBitsToFloatRoundTrip(): void
    {
        $testValues = [0.0, 1.0, -1.0, 2.0, 0.5, 1.5, -42.25, 1e10, 1e-10, PHP_FLOAT_MAX, INF, -INF];

        foreach ($testValues as $value) {
            $result = Floats::bitsToFloat(Floats::floatToBits($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test floatToBits and bitsToFloat round-trip with negative zero.
     */
    public function testFloatToBitsBitsToFloatRoundTripNegativeZero(): void
    {
        $result = Floats::bitsToFloat(Floats::floatToBits(-0.0));
        $this->assertTrue(Floats::isNegativeZero($result));
    }

    /**
     * Test floatToBits and bitsToFloat round-trip with NAN.
     */
    public function testFloatToBitsBitsToFloatRoundTripNan(): void
    {
        $bits = Floats::floatToBits(NAN);
        $result = Floats::bitsToFloat($bits);
        $this->assertTrue(is_nan($result));
    }

    #endregion

    #region Method disassemble() tests.

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
     * Test disassemble with NAN.
     */
    public function testDisassembleNan(): void
    {
        $result = Floats::disassemble(NAN);

        // NAN has exponent all 1s and non-zero fraction
        $this->assertSame(2047, $result['exponent']);
        $this->assertGreaterThan(0, $result['fraction']);
    }

    #endregion

    #region Method assemble() tests.

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
     * Test assemble with NAN (exponent 2047, non-zero fraction).
     */
    public function testAssembleNan(): void
    {
        $result = Floats::assemble(0, 2047, 1);
        $this->assertTrue(is_nan($result));
    }

    /**
     * Test assemble with invalid sign throws DomainException.
     */
    public function testAssembleInvalidSignThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign:');
        Floats::assemble(2, 1023, 0);
    }

    /**
     * Test assemble with negative sign throws DomainException.
     */
    public function testAssembleNegativeSignThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid sign:');
        Floats::assemble(-1, 1023, 0);
    }

    /**
     * Test assemble with invalid exponent throws DomainException.
     */
    public function testAssembleInvalidExponentThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid exponent:');
        Floats::assemble(0, 2048, 0);
    }

    /**
     * Test assemble with negative exponent throws DomainException.
     */
    public function testAssembleNegativeExponentThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid exponent:');
        Floats::assemble(0, -1, 0);
    }

    /**
     * Test assemble with invalid fraction throws DomainException.
     */
    public function testAssembleInvalidFractionThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid fraction:');
        Floats::assemble(0, 1023, 0x10000000000000); // 2^52, one too large
    }

    /**
     * Test assemble with negative fraction throws DomainException.
     */
    public function testAssembleNegativeFractionThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid fraction:');
        Floats::assemble(0, 1023, -1);
    }

    #endregion

    #region disassemble()/assemble() round-trip tests.

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

    #endregion

    #region Method ulp() tests.

    /**
     * Test ULP with standard values.
     */
    public function testUlpWithStandardValues(): void
    {
        // ULP of 1.0 should be PHP_FLOAT_EPSILON (1.0 is a power of 2).
        $this->assertSame(PHP_FLOAT_EPSILON, Floats::ulp(1.0));

        // ULP is defined as the gap to the next representable float.
        // For non-powers-of-2, the formula `value * PHP_FLOAT_EPSILON` is only approximate.
        $this->assertSame(Floats::nextUp(1000.0) - 1000.0, Floats::ulp(1000.0));
        $this->assertSame(Floats::nextUp(0.001) - 0.001, Floats::ulp(0.001));
    }

    /**
     * Test ULP with positive zero.
     */
    public function testUlpWithPositiveZero(): void
    {
        $expected = PHP_FLOAT_EPSILON * PHP_FLOAT_MIN;
        $this->assertSame($expected, Floats::ulp(0.0));
    }

    /**
     * Test ULP with negative zero.
     */
    public function testUlpWithNegativeZero(): void
    {
        $expected = PHP_FLOAT_EPSILON * PHP_FLOAT_MIN;
        $this->assertSame($expected, Floats::ulp(-0.0));
    }

    /**
     * Test ULP with negative values uses absolute value.
     */
    public function testUlpWithNegativeValues(): void
    {
        // ULP is the same for positive and negative values
        $this->assertSame(Floats::ulp(100.0), Floats::ulp(-100.0));
        $this->assertSame(Floats::ulp(1.0), Floats::ulp(-1.0));
    }

    /**
     * Test ULP with large values.
     */
    public function testUlpWithLargeValues(): void
    {
        $large = 1e20;
        $ulp = Floats::ulp($large);

        // ULP is defined as the gap to the next representable float.
        $this->assertSame(Floats::nextUp($large) - $large, $ulp);

        // Verify it's actually the spacing.
        $next = $large + $ulp;
        $this->assertGreaterThan($large, $next);
    }

    /**
     * Test ULP of ±PHP_FLOAT_MAX is the gap below it (2^971), not INF.
     */
    public function testUlpWithFloatMax(): void
    {
        $expected = 2.0 ** 971;
        $this->assertSame($expected, Floats::ulp(PHP_FLOAT_MAX));
        $this->assertSame($expected, Floats::ulp(-PHP_FLOAT_MAX));

        // It's the same spacing as just below PHP_FLOAT_MAX.
        $this->assertSame(Floats::ulp(Floats::nextDown(PHP_FLOAT_MAX)), Floats::ulp(PHP_FLOAT_MAX));
    }

    /**
     * Test ULP with small values.
     */
    public function testUlpWithSmallValues(): void
    {
        $small = 1e-100;
        $ulp = Floats::ulp($small);

        // ULP is defined as the gap to the next representable float.
        $this->assertSame(Floats::nextUp($small) - $small, $ulp);
    }

    /**
     * Test ULP with infinity returns INF.
     */
    public function testUlpWithInfinity(): void
    {
        $this->assertSame(INF, Floats::ulp(INF));
        $this->assertSame(INF, Floats::ulp(-INF));
    }

    /**
     * Test ULP with NAN returns NAN.
     */
    public function testUlpWithNan(): void
    {
        $this->assertNan(Floats::ulp(NAN));
    }

    /**
     * Test ULP relationship with nextUp().
     */
    public function testUlpRelationshipWithNext(): void
    {
        $value = 42.0;
        $ulp = Floats::ulp($value);
        $next = Floats::nextUp($value);

        // The difference should approximately equal the ULP
        // (may have rounding in floating-point subtraction)
        $diff = $next - $value;
        $this->assertGreaterThan(0, $diff);
        $this->assertLessThanOrEqual($ulp * 2, $diff);
    }

    #endregion
}
