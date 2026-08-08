<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Floats;

use DomainException;
use OceanMoon\Core\Enums\ExponentFormat;
use OceanMoon\Core\Enums\FloatFormat;
use OceanMoon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RoundingMode;

/**
 * Test class for Floats utility class - conversion methods.
 */
#[CoversClass(Floats::class)]
#[CoversClass(ExponentFormat::class)]
final class FloatsConversionTest extends TestCase
{
    #region Method toHex() tests.

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
