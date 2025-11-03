<?php

declare(strict_types = 1);

namespace Galaxon\Core\Tests;

// Throwables
use ValueError;
use DivisionByZeroError;

// PHPUnit
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

// Galaxon
use Galaxon\Core\Angle;

#[CoversClass(Angle::class)]
final class AngleTests extends TestCase
{
    /**
     * Assert that two float values are equal within a delta tolerance.
     *
     * @param float $expected The expected value.
     * @param float $actual The actual value.
     * @param float $delta The maximum difference allowed (default: RAD_EPSILON).
     */
    private function assertFloatEquals(float $expected, float $actual, float $delta = Angle::RAD_EPSILON): void
    {
        $this->assertEqualsWithDelta($expected, $actual, $delta);
    }

    /**
     * Assert that two Angle instances are equal.
     *
     * @param Angle $a The first angle.
     * @param Angle $b The second angle.
     */
    private function assertAngleEquals(Angle $a, Angle $b): void
    {
        $this->assertTrue($a->eq($b), "Angles differ: {$a} vs {$b}");
    }

    /**
     * Test that creating an angle with infinity throws ValueError.
     */
    public function testFactoryWithInfinity(): void
    {
        $this->expectException(ValueError::class);
        Angle::fromRadians(INF);
    }

    /**
     * Test that creating an angle with NaN throws ValueError.
     */
    public function testFactoryWithNaN(): void
    {
        $this->expectException(ValueError::class);
        Angle::fromDegrees(NAN);
    }

    /**
     * Test that all factory methods and getters work correctly together.
     *
     * Creates an angle using one factory method and verifies it can be converted
     * to all other units with correct values.
     */
    public function testFactoriesAndGettersRoundtrip(): void
    {
        $a = Angle::fromDegrees(180.0);
        $this->assertFloatEquals(M_PI, $a->toRadians());
        $this->assertFloatEquals(180.0, $a->toDegrees());
        $this->assertFloatEquals(200.0, $a->toGradians());
        $this->assertFloatEquals(0.5, $a->toTurns());
    }

    /**
     * Test conversion to and from degrees, arcminutes, and arcseconds (DMS).
     *
     * Verifies that DMS values round-trip correctly and that floating-point
     * precision near boundaries produces expected results.
     */
    public function testDmsRoundtripAndCarry(): void
    {
        $a = Angle::fromDegrees(12, 34, 56);
        [$d, $m, $s] = $a->toDegrees(Angle::UNIT_ARCSECOND);
        $this->assertFloatEquals(12.0, $d);
        $this->assertFloatEquals(34.0, $m);
        $this->assertFloatEquals(56.0, $s);

        // Verify floating-point precision at seconds and minutes boundaries.
        $b = Angle::fromDegrees(29.999999999);
        [$d2, $m2, $s2] = $b->toDegrees(Angle::UNIT_ARCSECOND);
        $this->assertFloatEquals(29, $d2);
        $this->assertFloatEquals(59, $m2);
        $this->assertFloatEquals(59.9999964, $s2);

        // Verify floating-point precision at minutes boundary.
        $b = Angle::fromDegrees(29.999999999);
        [$d3, $m3] = $b->toDegrees(Angle::UNIT_ARCMINUTE);
        $this->assertFloatEquals(29, $d3);
        $this->assertFloatEquals(59.99999994, $m3);

        // Test that invalid smallest unit index throws ValueError.
        $this->expectException(ValueError::class);
        $x = $b->toDegrees(3);
    }

    /**
     * Test parsing of angle strings in CSS units and DMS format.
     *
     * Verifies that the parse() method correctly handles rad, deg, grad, turn units
     * and DMS notation with both Unicode and ASCII symbols.
     */
    public function testParsingCssUnitsAndDms(): void
    {
        $this->assertAngleEquals(Angle::fromDegrees(12), Angle::parse('12deg'));
        $this->assertAngleEquals(Angle::fromDegrees(12), Angle::parse('12 DEG'));
        $this->assertAngleEquals(Angle::fromTurns(0.5), Angle::parse('0.5 turn'));
        $this->assertAngleEquals(Angle::fromRadians(M_PI), Angle::parse(M_PI . 'rad'));

        // Unicode symbols (°, ′, ″).
        $this->assertAngleEquals(Angle::fromDegrees(12, 34, 56), Angle::parse('12° 34′ 56″'));
        // ASCII fallback (°, ', ").
        $this->assertAngleEquals(Angle::fromDegrees(-12, -34, -56), Angle::parse("-12°34'56\""));
    }

    /**
     * Test that parsing empty or invalid input throws ValueError.
     */
    public function testParseRejectsBadInputs(): void
    {
        $this->expectException(ValueError::class);
        Angle::parse('');
    }

    /**
     * Test wrapping angles into unsigned [0, τ) and signed [-π, π) ranges.
     *
     * Verifies that the wrap() method correctly normalizes angles to the
     * appropriate range based on the signed parameter.
     */
    public function testWrapUnsignedAndSigned(): void
    {
        $a = Angle::fromRadians(2 * M_PI)->wrap();
        $this->assertFloatEquals(0.0, $a->toRadians());

        $b = Angle::fromRadians(M_PI)->wrap(true);
        // Signed range is [-π, π): π maps to -π.
        $this->assertFloatEquals(-M_PI, $b->toRadians());
    }

    /**
     * Test arithmetic operations (add, sub, mul, div).
     *
     * Verifies that angles can be added, subtracted, and scaled with correct results.
     */
    public function testArithmetic(): void
    {
        $a = Angle::fromDegrees(10);

        $sum = $a->add(Angle::fromDegrees(20));
        $this->assertFloatEquals(30.0, $sum->toDegrees());

        $diff = $a->sub(Angle::fromDegrees(40));
        $this->assertFloatEquals(-30.0, $diff->toDegrees());

        $scaled = $a->mul(3)->div(2);
        $this->assertFloatEquals(15.0, $scaled->toDegrees());
    }

    /**
     * Test that multiplying by infinity throws ValueError.
     */
    public function testMulWithNonFiniteParameter(): void
    {
        $a = Angle::fromDegrees(10);
        $this->expectException(ValueError::class);
        $a->mul(INF);
    }

    /**
     * Test that dividing by NaN throws ValueError.
     */
    public function testDivWithNonFiniteParameters(): void
    {
        $a = Angle::fromDegrees(10);
        $this->expectException(ValueError::class);
        $a->div(NAN);
    }

    /**
     * Test that wrapping with infinity throws ValueError.
     */
    public function testWrapWithNonFiniteParameters(): void
    {
        $this->expectException(ValueError::class);
        Angle::wrapDegrees(INF);
    }

    /**
     * Test trigonometric functions and their behavior at singularities.
     *
     * Verifies that sin, cos, tan return correct values and that tan
     * produces infinity at 90° as expected.
     */
    public function testTrigAndReciprocalsBehaviour(): void
    {
        $a = Angle::fromDegrees(60);
        $this->assertFloatEquals(sqrt(3) / 2, $a->sin());
        $this->assertFloatEquals(0.5, $a->cos());
        $this->assertFloatEquals(sqrt(3), $a->tan());

        // Verify that tan(90°) = ∞.
        $t = Angle::fromDegrees(90);
        $this->assertTrue(is_infinite($t->tan()));
    }

    /**
     * Test formatting angles in various output formats.
     *
     * Verifies that the format() method correctly produces strings in rad, deg,
     * grad, turn, and DMS formats with specified decimal precision.
     */
    public function testFormatVariants(): void
    {
        $a = Angle::fromDegrees(12.5);
        $this->assertSame('0.2181661565rad', $a->format('rad', 10));
        $this->assertSame('12.50deg', $a->format('deg', 2));
        $this->assertSame('13.888888889grad', $a->format('grad', 9));
        $this->assertSame('0.0347222222turn', $a->format('turn', 10));

        // DMS via format.
        $this->assertSame("12° 30′ 0″", $a->format('dms', 0));

        // Verify that negative decimals value throws ValueError.
        $this->expectException(ValueError::class);
        $a->format('rad', -1);
    }

    /**
     * Test DMS formatting when no carry is needed.
     *
     * Verifies that values just below rounding thresholds are formatted
     * correctly without triggering carry to the next unit.
     */
    public function testFormatDmsNoCarryNeeded(): void {
        // Values that shouldn't trigger carry
        $a = Angle::fromDegrees(29, 59, 59.994);
        $this->assertSame("29° 59′ 59.994″", $a->format('dms', 3));
    }

    /**
     * Test DMS formatting with carry logic across unit boundaries.
     *
     * Verifies that rounding causes correct carry from seconds to minutes,
     * minutes to degrees, and handles both positive and negative angles.
     */
    public function testFormatDmsWithCarry(): void {
        // Test degree rounding (29.9999... → 30°)
        $a = Angle::fromDegrees(29.9999999999);
        $this->assertSame("30.000°", $a->format('d', 3));
        $this->assertSame("30° 0.000′", $a->format('dm', 3));
        $this->assertSame("30° 0′ 0.000″", $a->format('dms', 3));

        // Test arcminute carry (29° 59.9999′ → 30° 0′)
        $a = Angle::fromDegrees(29, 59.9999999);
        $this->assertSame("30° 0.000′", $a->format('dm', 3));

        // Test arcsecond carry (29° 59′ 59.9999″ → 30° 0′ 0″)
        $a = Angle::fromDegrees(29, 59, 59.9999999);
        $this->assertSame("30° 0′ 0.000″", $a->format('dms', 3));

        // Test double carry (seconds → minutes → degrees)
        $a = Angle::fromDegrees(29, 59, 59.9999999);
        $this->assertSame("30.000°", $a->format('d', 3));

        // Test mid-range carry (not at zero boundary)
        $a = Angle::fromDegrees(45, 59, 59.9995);
        $this->assertSame("46° 0′ 0.000″", $a->format('dms', 3));

        // Test negative angle carry
        $a = Angle::fromDegrees(-29.9999999999);
        $this->assertSame("-30.000°", $a->format('d', 3));
        $this->assertSame("-30° 0.000′", $a->format('dm', 3));
        $this->assertSame("-30° 0′ 0.000″", $a->format('dms', 3));
    }

    /**
     * Set up the test environment with deterministic random seed.
     */
    protected function setUp(): void
    {
        // Deterministic randomness for reproducible tests.
        mt_srand(0xC0FFEE);
    }

    /**
     * Generate a random float in the specified range.
     *
     * @param float $min The minimum value (inclusive).
     * @param float $max The maximum value (exclusive).
     * @return float A random float in [min, max).
     */
    private function randFloat(float $min, float $max): float
    {
        // Uniform float in [min, max).
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }

    /**
     * Test random round-trip conversions between all angle units.
     *
     * Performs 500 randomized tests converting angles between radians, degrees,
     * gradians, and turns to verify conversion accuracy across a large range.
     */
    public function testRandomRoundtripsRadiansDegreesGradiansTurns(): void
    {
        for ($i = 0; $i < 500; $i++) {
            // Span a large range, including huge magnitudes.
            $rad = $this->randFloat(-1e6, 1e6);
            $a   = Angle::fromRadians($rad);

            // Verify toX() / fromX() round-trips.
            $this->assertFloatEquals($rad, Angle::fromRadians($a->toRadians())->toRadians());

            $deg = $a->toDegrees();
            $this->assertFloatEquals($a->toRadians(), Angle::fromDegrees($deg)->toRadians());

            $grad = $a->toGradians();
            $this->assertFloatEquals($a->toRadians(), Angle::fromGradians($grad)->toRadians());

            $turn = $a->toTurns();
            $this->assertFloatEquals($a->toRadians(), Angle::fromTurns($turn)->toRadians());
        }
    }

    /**
     * Test format-then-parse round-trips for all output styles.
     *
     * Performs 200 randomized tests formatting angles in all supported styles
     * (rad, deg, grad, turn, d, dm, dms) and parsing them back to verify
     * that no information is lost in the conversion.
     */
    public function testFormatThenParseRoundtripVariousStyles(): void
    {
        $styles = ['rad', 'deg', 'grad', 'turn', 'd', 'dm', 'dms'];

        for ($i = 0; $i < 200; $i++) {
            $rad = $this->randFloat(-1000.0, 1000.0);
            $a   = Angle::fromRadians($rad);

            foreach ($styles as $style) {
                // Use max float precision to ensure correct round-trip conversion.
                $s = $a->format($style, 17);
                $b = Angle::parse($s);

                $this->assertTrue(
                    $a->eq($b),
                    "Format/parse mismatch for style '{$style}': {$s} → {$b} vs {$a}"
                );
            }
        }
    }

    /**
     * Test wrapping behavior at boundary values.
     *
     * Verifies that wrapping correctly handles edge cases like 0, 2π, -π
     * in both unsigned [0, τ) and signed [-π, π) ranges.
     */
    public function testWrapBoundariesSignedAndUnsigned(): void
    {
        $twoPi = 2 * M_PI;

        // Unsigned [0, τ).
        $this->assertFloatEquals(0.0, Angle::wrapRadians(0.0, false));
        $this->assertFloatEquals(0.0, Angle::wrapRadians($twoPi, false));
        $this->assertFloatEquals(0.0, Angle::wrapRadians(-$twoPi, false));
        $this->assertFloatEquals(M_PI, Angle::wrapRadians(-M_PI, false));

        // Signed [-π, π).
        $this->assertFloatEquals(-M_PI, Angle::wrapRadians(M_PI, true));  // right edge maps to -π
        $this->assertFloatEquals(-M_PI, Angle::wrapRadians(-M_PI, true));
        $this->assertFloatEquals(0.0, Angle::wrapRadians($twoPi, true));
        $this->assertFloatEquals(0.0, Angle::wrapRadians(-$twoPi, true));

        // Verify that instance methods produce correct results.
        $a = Angle::fromRadians($twoPi)->wrap();
        $this->assertFloatEquals(0.0, $a->toRadians());
        $b = Angle::fromRadians(M_PI)->wrap(true);
        $this->assertFloatEquals(-M_PI, $b->toRadians());
    }

    /**
     * Test DMS conversion with extreme and out-of-range values.
     *
     * Verifies that fromDegrees() correctly handles arcminutes and arcseconds
     * beyond their normal ranges (0-59) and mixed sign values.
     */
    public function testDmsExtremesAndOutOfRangeParts(): void
    {
        // Minutes/seconds beyond their usual ranges should still compute correctly.
        $a = Angle::fromDegrees(10, 120, 120); // 10° + 2° + 0.033...° = 12.033...
        $this->assertFloatEquals(12.0333333333, $a->toDegrees(), 1e-9);

        // Mixed signs as documented (caller responsibility).
        $b = Angle::fromDegrees(-12, -90, 30); // -12 - 1.5 + 0.008333... = -13.491666...
        $this->assertFloatEquals(-13.4916666667, $b->toDegrees(), 1e-9);

        // Exactly 60 seconds (should carry in formatting).
        $a = Angle::fromDegrees(29, 59, 60.0);
        $this->assertSame("30° 0′ 0.000″", $a->format('dms', 3));
    }

    /**
     * Test parsing with various whitespace, case, and symbol variations.
     *
     * Verifies that the parser handles whitespace tolerance, case insensitivity,
     * both Unicode and ASCII symbols, and rejects invalid input.
     */
    public function testParsingWhitespaceAndCaseAndAsciiUnicodeSymbols(): void
    {
        $this->assertTrue(Angle::fromDegrees(12)->eq(Angle::parse('12 DEG')));
        $this->assertTrue(Angle::fromTurns(0.25)->eq(Angle::parse(' 0.25   turn ')));
        $this->assertTrue(Angle::fromRadians(M_PI)->eq(Angle::parse(sprintf('%.12frad', M_PI))));

        // Unicode DMS symbols (°, ′, ″).
        $this->assertTrue(Angle::fromDegrees(12, 34, 56)->eq(Angle::parse('12° 34′ 56″')));
        // ASCII DMS fallback (°, ', ").
        $this->assertTrue(Angle::fromDegrees(-12, -34, -56)->eq(Angle::parse("-12°34'56\"")));

        // Verify that invalid DMS format throws ValueError.
        $this->expectException(ValueError::class);
        $a = Angle::parse('-');
    }

    /**
     * Test the tryParse() method for both success and failure cases.
     *
     * Verifies that tryParse() returns true and sets the result for valid input,
     * and returns false with null result for invalid input without throwing.
     */
    public function testTryParseSuccessAndFailure(): void
    {
        $ok = Angle::tryParse('12deg', $a);
        $this->assertTrue($ok);
        $this->assertInstanceOf(Angle::class, $a);

        $bad = Angle::tryParse('not an angle', $b);
        $this->assertFalse($bad);
        $this->assertNull($b);
    }

    /**
     * Test that division by zero throws DivisionByZeroError.
     */
    public function testDivisionByZero(): void
    {
        $a = Angle::fromDegrees(90);
        $this->expectException(DivisionByZeroError::class);
        $a->div(0.0);
    }

    /**
     * Test comparison behavior with epsilon tolerance and sign of delta.
     *
     * Verifies that cmp() correctly returns -1, 0, or 1 based on the
     * difference between angles, and rejects negative epsilon values.
     */
    public function testCompareWithEpsilonAndDelta(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);

        // Delta is negative -> a < b.
        $this->assertSame(-1, $a->cmp($b));

        // Delta is positive -> b > a.
        $this->assertSame(1, $b->cmp($a));

        // Epsilon negative -> invalid argument.
        $this->expectException(ValueError::class);
        $a->cmp($b, -1e-9);
    }

    /**
     * Test that wrapGradians() normalizes values correctly.
     *
     * Verifies wrapping behavior for gradians in both unsigned [0, 400)
     * and signed [-200, 200) ranges.
     */
    public function testWrapGradiansBehaviour(): void
    {
        $this->assertFloatEquals(50.0, Angle::wrapGradians(450.0, false));
        $this->assertFloatEquals(190.0, Angle::wrapGradians(-210.0, true));
    }

    /**
     * Test that wrapDegrees() normalizes values correctly.
     *
     * Verifies wrapping behavior for degrees in both unsigned [0, 360)
     * and signed [-180, 180) ranges.
     */
    public function testWrapDegreesBehaviour(): void
    {
        $this->assertFloatEquals(50.0, Angle::wrapDegrees(410.0, false));
        $this->assertFloatEquals(150.0, Angle::wrapDegrees(-210.0, true));
    }

    /**
     * Test hyperbolic trigonometric functions.
     *
     * Verifies that sinh, cosh, and tanh methods return values matching
     * PHP's built-in hyperbolic functions.
     */
    public function testHyperbolicTrigFunctions(): void
    {
        $x = 0.5;
        $a = Angle::fromRadians($x);

        $this->assertFloatEquals(sinh($x), $a->sinh());
        $this->assertFloatEquals(cosh($x), $a->cosh());
        $this->assertFloatEquals(tanh($x), $a->tanh());
    }

    /**
     * Test that formatting with an invalid format string throws ValueError.
     */
    public function testFormatInvalidFormatString(): void {
        $a = Angle::fromDegrees(45);
        $this->expectException(ValueError::class);
        $a->format('invalid');
    }

    /**
     * Test the __toString() magic method.
     *
     * Verifies that casting an angle to string produces the expected
     * format (radians with CSS notation).
     */
    public function testToString(): void {
        $a = Angle::fromRadians(M_PI);
        $this->assertMatchesRegularExpression('/^\d+\.\d+rad$/', (string)$a);
    }

    /**
     * Test the eq() equality method.
     *
     * Verifies that eq() correctly identifies equal and unequal angles.
     */
    public function testEquals(): void {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($a->eq($c));
        $this->assertFalse($a->eq($b));
    }
}
