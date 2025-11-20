<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use DivisionByZeroError;
use Galaxon\Core\Angle;
use Galaxon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

#[CoversClass(Angle::class)]
final class AngleTest extends TestCase
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
        $this->assertTrue($a->equals($b), "Angles differ: {$a} vs {$b}");
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
        [$d, $m, $s] = $a->toDMS(Angle::UNIT_ARCSECOND);
        $this->assertFloatEquals(12.0, $d);
        $this->assertFloatEquals(34.0, $m);
        $this->assertFloatEquals(56.0, $s);

        // Verify floating-point precision at seconds and minutes boundaries.
        $b = Angle::fromDegrees(29.999999999);
        [$d2, $m2, $s2] = $b->toDMS(Angle::UNIT_ARCSECOND);
        $this->assertFloatEquals(29, $d2);
        $this->assertFloatEquals(59, $m2);
        $this->assertFloatEquals(59.9999964, $s2);

        // Verify floating-point precision at minutes boundary.
        $b = Angle::fromDegrees(29.999999999);
        [$d3, $m3] = $b->toDMS(Angle::UNIT_ARCMINUTE);
        $this->assertFloatEquals(29, $d3);
        $this->assertFloatEquals(59.99999994, $m3);

        // Test that invalid smallest unit index throws ValueError.
        $this->expectException(ValueError::class);
        $x = $b->toDMS(3);
    }

    /**
     * Test toDMS() with degrees only (UNIT_DEGREE).
     *
     * Verifies that requesting only degrees returns a single-element array
     * with the correct decimal degree value.
     */
    public function testToDmsWithDegreesOnly(): void
    {
        $a = Angle::fromDegrees(45.5);
        [$d] = $a->toDMS(Angle::UNIT_DEGREE);
        $this->assertFloatEquals(45.5, $d);
    }

    /**
     * Test toDMS() with negative angles.
     *
     * Verifies that negative angles correctly apply the sign to all components
     * when converted to DMS format.
     */
    public function testToDmsWithNegativeAngles(): void
    {
        $a = Angle::fromDegrees(-12, -34, -56);

        // Test arcseconds
        [$d, $m, $s] = $a->toDMS(Angle::UNIT_ARCSECOND);
        $this->assertFloatEquals(-12.0, $d);
        $this->assertFloatEquals(-34.0, $m);
        $this->assertFloatEquals(-56.0, $s);

        // Test arcminutes
        [$d2, $m2] = $a->toDMS(Angle::UNIT_ARCMINUTE);
        $this->assertFloatEquals(-12.0, $d2);
        $this->assertFloatEquals(-34.933333, $m2, 1e-6);
    }

    /**
     * Test toDMS() with zero angle.
     *
     * Verifies that a zero angle converts correctly to DMS format with
     * all zero components.
     */
    public function testToDmsWithZeroAngle(): void
    {
        $a = Angle::fromDegrees(0);
        [$d, $m, $s] = $a->toDMS(Angle::UNIT_ARCSECOND);
        $this->assertFloatEquals(0.0, $d);
        $this->assertFloatEquals(0.0, $m);
        $this->assertFloatEquals(0.0, $s);
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
     * Test wrapping angles into unsigned and signed ranges with non-boundary values.
     *
     * Verifies that the wrap() method correctly normalizes angles to the
     * appropriate range. This tests general behavior, not boundary conditions.
     */
    public function testWrapUnsignedAndSigned(): void
    {
        // Unsigned range [0, τ) - test values in the middle of ranges
        $a = Angle::fromRadians(3 * M_PI);  // 1.5 turns
        $this->assertFloatEquals(M_PI, $a->wrap(false)->toRadians());

        $b = Angle::fromRadians(-3 * M_PI / 2);  // -0.75 turns
        $this->assertFloatEquals(M_PI / 2, $b->wrap(false)->toRadians());

        // Signed range (-π, π] - test values in quadrants
        $c = Angle::fromRadians(5 * M_PI / 4);  // 225 degrees, should wrap to -135 degrees
        $this->assertFloatEquals(-3 * M_PI / 4, $c->wrap(true)->toRadians());

        $d = Angle::fromRadians(-5 * M_PI / 4);  // -225 degrees, should wrap to 135 degrees
        $this->assertFloatEquals(3 * M_PI / 4, $d->wrap(true)->toRadians());
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
        $this->assertSame('12° 30′ 0″', $a->format('dms', 0));

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
    public function testFormatDmsNoCarryNeeded(): void
    {
        // Values that shouldn't trigger carry
        $a = Angle::fromDegrees(29, 59, 59.994);
        $this->assertSame('29° 59′ 59.994″', $a->format('dms', 3));
    }

    /**
     * Test DMS formatting with carry logic across unit boundaries.
     *
     * Verifies that rounding causes correct carry from seconds to minutes,
     * minutes to degrees, and handles both positive and negative angles.
     */
    public function testFormatDmsWithCarry(): void
    {
        // Test degree rounding (29.9999... → 30°)
        $a = Angle::fromDegrees(29.9999999999);
        $this->assertSame('30.000°', $a->format('d', 3));
        $this->assertSame('30° 0.000′', $a->format('dm', 3));
        $this->assertSame('30° 0′ 0.000″', $a->format('dms', 3));

        // Test arcminute carry (29° 59.9999′ → 30° 0′)
        $a = Angle::fromDegrees(29, 59.9999999);
        $this->assertSame('30° 0.000′', $a->format('dm', 3));

        // Test arcsecond carry (29° 59′ 59.9999″ → 30° 0′ 0″)
        $a = Angle::fromDegrees(29, 59, 59.9999999);
        $this->assertSame('30° 0′ 0.000″', $a->format('dms', 3));

        // Test double carry (seconds → minutes → degrees)
        $a = Angle::fromDegrees(29, 59, 59.9999999);
        $this->assertSame('30.000°', $a->format('d', 3));

        // Test mid-range carry (not at zero boundary)
        $a = Angle::fromDegrees(45, 59, 59.9995);
        $this->assertSame('46° 0′ 0.000″', $a->format('dms', 3));

        // Test negative angle carry
        $a = Angle::fromDegrees(-29.9999999999);
        $this->assertSame('-30.000°', $a->format('d', 3));
        $this->assertSame('-30° 0.000′', $a->format('dm', 3));
        $this->assertSame('-30° 0′ 0.000″', $a->format('dms', 3));
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
     * Test random round-trip conversions between all angle units.
     *
     * Performs 500 randomized tests converting angles between radians, degrees,
     * gradians, and turns to verify conversion accuracy across a large range.
     */
    public function testRandomRoundtripsRadiansDegreesGradiansTurns(): void
    {
        for ($i = 0; $i < 500; $i++) {
            // Span a large range, including huge magnitudes.
            $rad = Floats::randInRange(-1e6, 1e6);
            $a = Angle::fromRadians($rad);

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
            $rad = Floats::randInRange(-1000.0, 1000.0);
            $a = Angle::fromRadians($rad);

            foreach ($styles as $style) {
                // Use max float precision to ensure correct round-trip conversion.
                $s = $a->format($style, 17);
                $b = Angle::parse($s);

                $this->assertTrue(
                    $a->equals($b),
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
        // Unsigned [0, τ).
        $this->assertFloatEquals(0.0, Angle::wrapRadians(0.0, false));
        $this->assertFloatEquals(0.0, Angle::wrapRadians(Angle::TAU, false));
        $this->assertFloatEquals(0.0, Angle::wrapRadians(-Angle::TAU, false));
        $this->assertFloatEquals(M_PI, Angle::wrapRadians(-M_PI, false));

        // Signed (-π, π].
        $this->assertFloatEquals(M_PI, Angle::wrapRadians(-M_PI, true));
        $this->assertFloatEquals(M_PI, Angle::wrapRadians(M_PI, true));
        $this->assertFloatEquals(0.0, Angle::wrapRadians(Angle::TAU, true));
        $this->assertFloatEquals(0.0, Angle::wrapRadians(-Angle::TAU, true));

        // Verify that instance methods produce correct results.
        $a = Angle::fromRadians(Angle::TAU)->wrap();
        $this->assertFloatEquals(0.0, $a->toRadians());
        $b = Angle::fromRadians(-M_PI)->wrap(true);
        $this->assertFloatEquals(M_PI, $b->toRadians());
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
        $this->assertSame('30° 0′ 0.000″', $a->format('dms', 3));
    }

    /**
     * Test parsing with various whitespace, case, and symbol variations.
     *
     * Verifies that the parser handles whitespace tolerance, case insensitivity,
     * both Unicode and ASCII symbols, and rejects invalid input.
     */
    public function testParsingWhitespaceAndCaseAndAsciiUnicodeSymbols(): void
    {
        $this->assertTrue(Angle::fromDegrees(12)->equals(Angle::parse('12 DEG')));
        $this->assertTrue(Angle::fromTurns(0.25)->equals(Angle::parse(' 0.25   turn ')));
        $this->assertTrue(Angle::fromRadians(M_PI)->equals(Angle::parse(sprintf('%.12frad', M_PI))));

        // Unicode DMS symbols (°, ′, ″).
        $this->assertTrue(Angle::fromDegrees(12, 34, 56)->equals(Angle::parse('12° 34′ 56″')));
        // ASCII DMS fallback (°, ', ").
        $this->assertTrue(Angle::fromDegrees(-12, -34, -56)->equals(Angle::parse("-12°34'56\"")));

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
     * Verifies that compare() correctly returns -1, 0, or 1 based on the
     * difference between angles.
     */
    public function testCompareWithEpsilonAndDelta(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);

        // Delta is negative -> a < b.
        $this->assertSame(-1, $a->compare($b));

        // Delta is positive -> b > a.
        $this->assertSame(1, $b->compare($a));
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
    public function testFormatInvalidFormatString(): void
    {
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
    public function testToString(): void
    {
        $a = Angle::fromRadians(M_PI);
        $this->assertMatchesRegularExpression('/^\d+\.\d+rad$/', (string)$a);
    }

    /**
     * Test the equals() equality method.
     *
     * Verifies that equals() correctly identifies equal and unequal angles.
     */
    public function testEquals(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($a->equals($c));
        $this->assertFalse($a->equals($b));
    }

    /**
     * Test equals() with epsilon tolerance.
     *
     * Verifies that angles differing by less than RAD_EPSILON are considered equal.
     */
    public function testEqualsWithEpsilonTolerance(): void
    {
        $a = Angle::fromRadians(1.0);
        $b = Angle::fromRadians(1.0 + Angle::RAD_EPSILON / 2);

        // Should be equal within epsilon
        $this->assertTrue($a->equals($b));

        // Should not be equal outside epsilon
        $c = Angle::fromRadians(1.0 + Angle::RAD_EPSILON * 2);
        $this->assertFalse($a->equals($c));
    }

    /**
     * Test equals() with non-Angle types returns false.
     *
     * Verifies that equals() gracefully handles invalid types without throwing.
     */
    public function testEqualsWithInvalidType(): void
    {
        $a = Angle::fromDegrees(10);

        $this->assertFalse($a->equals(10));
        $this->assertFalse($a->equals(10.0));
        $this->assertFalse($a->equals('10deg'));
        $this->assertFalse($a->equals([]));
        $this->assertFalse($a->equals(new \stdClass()));
    }

    /**
     * Test compare() with equal angles within epsilon.
     *
     * Verifies that compare() returns 0 for angles within epsilon tolerance.
     */
    public function testCompareEqualWithinEpsilon(): void
    {
        $a = Angle::fromRadians(1.0);
        $b = Angle::fromRadians(1.0 + Angle::RAD_EPSILON / 2);

        $this->assertSame(0, $a->compare($b));
    }

    /**
     * Test compare() throws TypeError for non-Angle types.
     *
     * Verifies that compare() throws TypeError when comparing with invalid types.
     */
    public function testCompareWithInvalidTypeThrows(): void
    {
        $a = Angle::fromDegrees(10);

        $this->expectException(\TypeError::class);
        $a->compare(10);
    }

    /**
     * Test compare() throws TypeError for string.
     */
    public function testCompareWithStringThrows(): void
    {
        $a = Angle::fromDegrees(10);

        $this->expectException(\TypeError::class);
        $a->compare('10deg');
    }

    /**
     * Test compare() throws TypeError for object.
     */
    public function testCompareWithObjectThrows(): void
    {
        $a = Angle::fromDegrees(10);

        $this->expectException(\TypeError::class);
        $a->compare(new \stdClass());
    }

    /**
     * Test isLessThan() method from Comparable trait.
     */
    public function testIsLessThan(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($a->isLessThan($b));
        $this->assertFalse($b->isLessThan($a));
        $this->assertFalse($a->isLessThan($c)); // Equal, not less than
    }

    /**
     * Test isLessThanOrEqual() method from Comparable trait.
     */
    public function testIsLessThanOrEqual(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($a->isLessThanOrEqual($b));
        $this->assertTrue($a->isLessThanOrEqual($c)); // Equal counts as <=
        $this->assertFalse($b->isLessThanOrEqual($a));
    }

    /**
     * Test isGreaterThan() method from Comparable trait.
     */
    public function testIsGreaterThan(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($b->isGreaterThan($a));
        $this->assertFalse($a->isGreaterThan($b));
        $this->assertFalse($a->isGreaterThan($c)); // Equal, not greater than
    }

    /**
     * Test isGreaterThanOrEqual() method from Comparable trait.
     */
    public function testIsGreaterThanOrEqual(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(20);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($b->isGreaterThanOrEqual($a));
        $this->assertTrue($a->isGreaterThanOrEqual($c)); // Equal counts as >=
        $this->assertFalse($a->isGreaterThanOrEqual($b));
    }

    /**
     * Test comparison methods with negative angles.
     */
    public function testComparisonWithNegativeAngles(): void
    {
        $a = Angle::fromDegrees(-30);
        $b = Angle::fromDegrees(-10);
        $c = Angle::fromDegrees(10);

        $this->assertTrue($a->isLessThan($b));
        $this->assertTrue($b->isLessThan($c));
        $this->assertTrue($c->isGreaterThan($a));
    }

    /**
     * Test comparison methods with wrapped vs unwrapped angles.
     */
    public function testComparisonRawVsWrapped(): void
    {
        $a = Angle::fromDegrees(10);
        $b = Angle::fromDegrees(370); // 10° + 360°

        // Raw comparison: 370° > 10°
        $this->assertTrue($b->isGreaterThan($a));

        // After wrapping: both become 10° (unsigned)
        $aWrapped = Angle::fromDegrees(10)->wrap();
        $bWrapped = Angle::fromDegrees(370)->wrap();
        $this->assertTrue($aWrapped->equals($bWrapped));
    }
}
