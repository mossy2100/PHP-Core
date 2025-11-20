<?php

declare(strict_types=1);

namespace Galaxon\Core;

use DivisionByZeroError;
use Override;
use Stringable;
use Throwable;
use TypeError;
use ValueError;

class Angle implements Stringable, Equatable
{
    use Comparable;

    // region Constants

    // Define τ = 2π.
    public const float TAU = 2 * M_PI;

    // Radians.
    public const float RADIANS_PER_TURN = self::TAU;
    public const float DEGREES_PER_RADIAN = 180 / M_PI;
    public const float ARCMINUTES_PER_RADIAN = 10800 / M_PI;
    public const float ARCSECONDS_PER_RADIAN = 648000 / M_PI;

    // Degrees, arcminutes, arcseconds.
    public const float DEGREES_PER_TURN = 360;
    public const float ARCMINUTES_PER_DEGREE = 60;
    public const float ARCSECONDS_PER_ARCMINUTE = 60;
    public const float ARCSECONDS_PER_DEGREE = 3600;

    // Gradians.
    public const float GRADIANS_PER_TURN = 400;
    public const float GRADIANS_PER_RADIAN = 200 / M_PI;
    public const float DEGREES_PER_GRADIAN = 0.9;

    // Epsilons for comparisons.
    public const float RAD_EPSILON = 1e-9;
    public const float TRIG_EPSILON = 1e-12;

    // Constants for use as smallest unit arguments.
    public const int UNIT_DEGREE = 0;
    public const int UNIT_ARCMINUTE = 1;
    public const int UNIT_ARCSECOND = 2;

    // endregion

    // region Properties

    /**
     * Internal storage in radians.
     *
     * @var float
     */
    private float $radians;

    // endregion

    // region Constructor and factory methods

    /**
     * Private constructor to enforce factory usage.
     *
     * @param float $radians The angle in radians.
     * @throws ValueError If the argument is a non-finite number.
     */
    private function __construct(float $radians)
    {
        // Guard.
        if (!is_finite($radians)) {
            throw new ValueError('Angle size cannot be ±∞ or NaN.');
        }

        $this->radians = $radians;
    }

    /**
     * Create an angle from radians.
     *
     * @param float $radians The angle in radians.
     * @return self The angle instance.
     * @throws ValueError If the argument is a non-finite number.
     */
    public static function fromRadians(float $radians): self
    {
        return new self($radians);
    }

    /**
     * Create an angle from degrees, arcminutes, and arcseconds.
     *
     * NB: In theory all parts SHOULD be either non-negative (i.e. 0 or positive) or non-positive (i.e. 0 or negative).
     * However, this is not enforced. Neither do any of the values have to be within a certain range (e.g. 0-60 for
     * minutes or seconds).
     *
     * So, for example, if you want to convert -12° 34′ 56″ to degrees, call fromDegrees(-12, -34, -56)
     * If you want to convert -12° 56″ to degrees, call fromDegrees(-12, 0, -56).
     *
     * @param float $degrees The degrees part.
     * @param float $arcmin The arcminutes part (optional).
     * @param float $arcsec The arcseconds part (optional).
     * @return self A new angle with a magnitude equal to the provided angle.
     * @throws ValueError If any of the arguments are non-finite numbers.
     */
    public static function fromDegrees(float $degrees, float $arcmin = 0.0, float $arcsec = 0.0): self
    {
        // Compute the total degrees.
        $total_deg = $degrees + $arcmin / self::ARCMINUTES_PER_DEGREE + $arcsec / self::ARCSECONDS_PER_DEGREE;
        return new self($total_deg / self::DEGREES_PER_RADIAN);
    }

    /**
     * Create an angle from gradians.
     *
     * @param float $gradians The angle in gradians.
     * @return self The angle instance.
     * @throws ValueError If the argument is a non-finite number.
     */
    public static function fromGradians(float $gradians): self
    {
        return new self($gradians / self::GRADIANS_PER_RADIAN);
    }

    /**
     * Create an angle from turns (full rotations).
     *
     * @param float $turns The angle in turns.
     * @return self The angle instance.
     * @throws ValueError If the argument is a non-finite number.
     */
    public static function fromTurns(float $turns): self
    {
        return new self($turns * self::RADIANS_PER_TURN);
    }

    /**
     * Checks that the input string, which is meant to indicate an angle, is valid.
     *
     * Different units (deg, rad, grad, turn) are supported, as used in CSS.
     * There can be spaces between the number and the unit.
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/angle
     *
     * Symbols for degrees, arcminutes, and arcseconds are also supported.
     * There cannot be any space between a number and its unit, but it's ok to have a single space
     * between two parts.
     *
     * If valid, the angle is returned; otherwise, an exception is thrown.
     *
     * @param string $value The string to parse.
     * @return self A new angle equivalent to the provided string.
     * @throws ValueError If the string does not represent a valid angle.
     */
    public static function parse(string $value): self
    {
        // Prepare an error message with the original value.
        $err_msg = "The provided string '$value' does not represent a valid angle.";

        // Reject empty input.
        $value = trim($value);
        if ($value === '') {
            throw new ValueError($err_msg);
        }

        // Check for a format containing symbols for degrees, arcminutes, and arcseconds.
        $num = '(?:\d+(?:\.\d+)?|\.\d+)';
        $pattern = "/^(?:(?P<sign>[-+]?)\s*)?"
                   . "(?:(?P<deg>$num)°\s*)?"
                   . "(?:(?P<min>$num)[′']\s*)?"
                   . "(?:(?P<sec>$num)[″\"])?$/u";
        if (preg_match($pattern, $value, $matches)) {
            // Require at least one component (deg/min/sec).
            if (empty($matches['deg']) && empty($matches['min']) && empty($matches['sec'])) {
                throw new ValueError($err_msg);
            }

            // Get the sign.
            $sign = isset($matches['sign']) && $matches['sign'] === '-' ? -1 : 1;

            // Extract the parts.
            $d = isset($matches['deg']) ? $sign * (float)$matches['deg'] : 0.0;
            $m = isset($matches['min']) ? $sign * (float)$matches['min'] : 0.0;
            $s = isset($matches['sec']) ? $sign * (float)$matches['sec'] : 0.0;

            // Convert to angle.
            return self::fromDegrees($d, $m, $s);
        }

        // Check for a format with CSS angle units.
        if (preg_match("/^(-?$num)\s*(rad|deg|grad|turn)$/i", $value, $m)) {
            $num = (float)$m[1];
            return match (strtolower($m[2])) {
                'rad'   => self::fromRadians($num),
                'deg'   => self::fromDegrees($num),
                'grad'  => self::fromGradians($num),
                'turn'  => self::fromTurns($num),
                default => throw new ValueError($err_msg),
            };
        }

        // No valid units.
        throw new ValueError($err_msg);
    }

    /**
     * Attempts to parse an angle string without throwing.
     *
     * On success, sets $result to a new Angle and returns true.
     * On failure, sets $result to null and returns false.
     *
     * @param string $value The input string to parse.
     * @param ?self &$result The parsed Angle on success; null on failure.
     * @return bool True if parsing succeeded; false otherwise.
     */
    public static function tryParse(string $value, ?self &$result): bool
    {
        try {
            $result = self::parse($value);
            return true;
        } catch (Throwable) {
            $result = null;
            return false;
        }
    }

    // endregion

    // region Methods for getting the angle in different units

    /**
     * Get the angle in radians.
     *
     * @return float The angle in radians.
     */
    public function toRadians(): float
    {
        return $this->radians;
    }

    /**
     * Get the angle in degrees, arcminutes, and arcseconds.
     * The result will be an array with 1-3 values, depending on the requested smallest unit.
     * Only the last item may have a fractional part; others will be whole numbers.
     *
     * If the angle is positive, the resulting values will all be positive.
     * If the angle is zero, the resulting values will all be zero.
     * If the angle is negative, the resulting values will all be negative.
     *
     * For the $smallest_unit parameter, you can use the UNIT_* class constants, i.e.
     * - UNIT_DEGREE for degrees only
     * - UNIT_ARCMINUTE for degrees and arcminutes
     * - UNIT_ARCSECOND for degrees, arcminutes, and arcseconds
     *
     * @param int $smallest_unit 0 for degrees, 1 for arcminutes, 2 for arcseconds (default).
     * @return float[] An array of 1-3 floats with the degrees, arcminutes, and arcseconds.
     * @throws ValueError If $smallest_unit is not 0, 1, or 2.
     */
    public function toDMS(int $smallest_unit = self::UNIT_ARCSECOND): array
    {
        $a = $this->radians * self::DEGREES_PER_RADIAN;
        $sign = Numbers::sign($a, false);
        $a = abs($a);

        switch ($smallest_unit) {
            case self::UNIT_DEGREE:
                $d = $a;

                // Apply sign and normalize -0.0 to 0.0.
                $d = Floats::normalizeZero($d * $sign);

                return [$d];

            case self::UNIT_ARCMINUTE:
                // Convert the total degrees to degrees and minutes (non-negative).
                $d = floor($a);
                $m = ($a - $d) * self::ARCMINUTES_PER_DEGREE;

                // Apply sign and normalize -0.0 to 0.0.
                $d = Floats::normalizeZero($d * $sign);
                $m = Floats::normalizeZero($m * $sign);

                return [$d, $m];

            case self::UNIT_ARCSECOND:
                // Convert the total degrees to degrees, minutes, and seconds (non-negative).
                $d = floor($a);
                $f_min = ($a - $d) * self::ARCMINUTES_PER_DEGREE;
                $m = floor($f_min);
                $s = ($f_min - $m) * self::ARCSECONDS_PER_ARCMINUTE;

                // Apply sign and normalize -0.0 to 0.0.
                $d = Floats::normalizeZero($d * $sign);
                $m = Floats::normalizeZero($m * $sign);
                $s = Floats::normalizeZero($s * $sign);

                return [$d, $m, $s];

            default:
                throw new ValueError(
                    'The smallest unit must be 0 for degrees (default), 1 for arcminutes, or 2 for arcseconds.'
                );
        }
    }

    /**
     * Get the angle in degrees.
     *
     * @return float The angle in degrees.
     */
    public function toDegrees(): float
    {
        return $this->toDMS(self::UNIT_DEGREE)[0];
    }

    /**
     * Get the angle in gradians.
     *
     * @return float The angle in gradians.
     */
    public function toGradians(): float
    {
        return $this->radians * self::GRADIANS_PER_RADIAN;
    }

    /**
     * Get the angle in turns.
     *
     * @return float The angle in turns.
     */
    public function toTurns(): float
    {
        return $this->radians / self::RADIANS_PER_TURN;
    }

    // endregion

    // region Arithmetic methods

    /**
     * Add another angle to this angle.
     *
     * @param self $other The angle to add.
     * @return self The sum as a new angle.
     */
    public function add(self $other): self
    {
        return new self($this->radians + $other->radians);
    }

    /**
     * Subtract another angle from this angle.
     *
     * @param self $other The angle to subtract.
     * @return self The difference as a new angle.
     */
    public function sub(self $other): self
    {
        return new self($this->radians - $other->radians);
    }

    /**
     * Multiply this angle by a factor.
     *
     * @param float $k The scale factor.
     * @return self The scaled angle.
     * @throws ValueError If the multiplier is a non-finite number.
     */
    public function mul(float $k): self
    {
        // Guard.
        if (!is_finite($k)) {
            throw new ValueError('Multiplier cannot be ±∞ or NaN.');
        }

        return new self($this->radians * $k);
    }

    /**
     * Divide this angle by a factor.
     *
     * @param float $k The scale factor.
     * @return self The scaled angle.
     * @throws DivisionByZeroError If the divisor is 0.
     * @throws ValueError If the divisor is a non-finite number.
     */
    public function div(float $k): self
    {
        // Guards.
        if ($k === 0.0) {
            throw new DivisionByZeroError('Divisor cannot be 0.');
        }
        if (!is_finite($k)) {
            throw new ValueError('Divisor cannot be ±∞ or NaN.');
        }

        return new self(fdiv($this->radians, $k));
    }

    /**
     * Get the absolute value of this angle.
     *
     * @return self A new angle with a non-negative magnitude.
     */
    public function abs(): self
    {
        return new self(abs($this->radians));
    }

    // endregion

    // region Comparison methods

    /**
     * Compare angles by their raw numeric values.
     *
     * Compares angles as numerical values without normalization. This means 360° > 0° even though they represent the
     * same angular position.
     * If you need to compare angular positions (where 0° = 360°), normalize both angles using wrap() before comparing.
     *
     * @param mixed $other The value to compare with.
     * @return int -1 if this < other, 0 if equal, 1 if this > other.
     * @throws TypeError If the value to compare with is not an Angle.
     *
     * @example
     * $a = Angle::fromDegrees(10);
     * $b = Angle::fromDegrees(350);
     * $a->compare($b); // -1 (10 < 350)
     *
     * // To compare as positions (accounting for wraparound):
     * $a->wrap()->compare($b->wrap()); // Still -1 (10 < 350 in unsigned range)
     * $a->wrap(true)->compare($b->wrap(true)); // 1 (10 > -10 in signed range)
     */
    #[Override]
    public function compare(mixed $other): int
    {
        // Check we're comparing two Angles.
        if (!$other instanceof self) {
            throw new TypeError('Object to compare with must be an Angle.');
        }

        // Check for equality within a reasonable tolerance.
        if (abs($this->radians - $other->radians) < self::RAD_EPSILON) {
            return 0;
        }

        // Check for less than or greater than.
        return $this->radians < $other->radians ? -1 : 1;
    }

    // endregion

    // region Trigonometry methods

    /**
     * Sine of the angle.
     *
     * @return float The sine value.
     */
    public function sin(): float
    {
        return sin($this->radians);
    }

    /**
     * Cosine of the angle.
     *
     * @return float The cosine value.
     */
    public function cos(): float
    {
        return cos($this->radians);
    }

    /**
     * Tangent of the angle.
     *
     * @return float The tangent value.
     */
    public function tan(): float
    {
        $s = sin($this->radians);
        $c = cos($this->radians);

        // If cos is effectively zero, return ±INF (sign chosen by the side, i.e., sign of sine).
        // The built-in tan() function normally doesn't ever return ±INF.
        if (abs($c) < self::TRIG_EPSILON) {
            return Numbers::copySign(INF, $s);
        }

        // Otherwise do IEEE‑754 division (no warnings/exceptions).
        return fdiv($s, $c);
    }

    /**
     * Get the hyperbolic sine of the angle.
     *
     * @return float The hyperbolic sine value.
     */
    public function sinh(): float
    {
        return sinh($this->radians);
    }

    /**
     * Get the hyperbolic cosine of the angle.
     *
     * @return float The hyperbolic cosine value.
     */
    public function cosh(): float
    {
        return cosh($this->radians);
    }

    /**
     * Get the hyperbolic tangent of the angle.
     *
     * @return float The hyperbolic tangent value.
     */
    public function tanh(): float
    {
        return tanh($this->radians);
    }

    // endregion

    // region Wrap methods

    // region Static methods

    /**
     * Normalize a scalar angle value into a specified half-open interval.
     *
     * This is a private method called from the public wrap[Unit]() methods.
     *
     * The range of values varies depending on the $units_per_turn parameter *and* the $signed flag.
     * 1. If $signed is true (default), the range is (-$units_per_turn/2, $units_per_turn/2].
     * NB: This means the minimum value is *excluded* in the range, while the maximum value is *included*.
     * 2. If $signed is false, the range is [0, $units_per_turn).
     * NB: This means the minimum value is *included* in the range, while the maximum value is *excluded*.
     * This may seem counterintuitive, but it's consistent with mathematical conventions.
     * @see https://en.wikipedia.org/wiki/Principal_value#Complex_argument
     *
     * @param float $value The value to wrap.
     * @param float $units_per_turn Units per full turn (e.g., τ for radians, 360 for degrees, 400 for gradians).
     * @param bool $signed Whether to return a signed range instead of the default positive range.
     * @return float The wrapped value.
     * @throws ValueError If the $value argument is non-finite.
     */
    private static function wrapAngle(float $value, float $units_per_turn, bool $signed = true): float
    {
        // Guard.
        if (!is_finite($value)) {
            throw new ValueError('Value must be finite.');
        }

        // Reduce using fmod to avoid large magnitudes.
        $r = fmod($value, $units_per_turn);

        // Adjust to fit within range bounds.
        // The value may be outside the range due to the sign of $value or the value of $signed.
        if ($signed) {
            // Signed range is (-$half, $half]
            $half = $units_per_turn / 2.0;
            if ($r <= -$half) {
                $r += $units_per_turn;
            } elseif ($r > $half) {
                $r -= $units_per_turn;
            }
        } else {
            // Unsigned range is [0, $units_per_turn)
            if ($r < 0.0) {
                $r += $units_per_turn;
            } elseif ($r >= $units_per_turn) {
                $r -= $units_per_turn;
            }
        }

        // Canonicalize -0.0 to 0.0.
        return Floats::normalizeZero($r);
    }

    /**
     * Normalize radians into [0, τ) or (-π, π].
     *
     * @param float $radians The angle in radians.
     * @param bool $signed Whether to return a signed range.
     * @return float The normalized angle in radians.
     */
    public static function wrapRadians(float $radians, bool $signed = true): float
    {
        return self::wrapAngle($radians, self::TAU, $signed);
    }

    /**
     * Normalize degrees into [0, 360) or (-180, 180].
     *
     * @param float $degrees The angle in degrees.
     * @param bool $signed Whether to return a signed range.
     * @return float The normalized angle in degrees.
     */
    public static function wrapDegrees(float $degrees, bool $signed = true): float
    {
        return self::wrapAngle($degrees, self::DEGREES_PER_TURN, $signed);
    }

    /**
     * Normalize gradians into [0, 400) or (-200, 200].
     *
     * @param float $gradians The angle in gradians.
     * @param bool $signed Whether to return a signed range.
     * @return float The normalized angle in gradians.
     */
    public static function wrapGradians(float $gradians, bool $signed = true): float
    {
        return self::wrapAngle($gradians, self::GRADIANS_PER_TURN, $signed);
    }

    // endregion

    // region Instance method

    /**
     * Normalize an angle to a specified range.
     *
     * This method modifies the current instance and returns $this for chaining.
     *
     * If $signed is false (default), the range is [0, τ).
     * If $signed is true, the range is [-π, π).
     *
     * @param bool $signed Whether to use a signed range, or the default positive range.
     * @return $this The current angle instance.
     *
     * @example
     * $alpha = Angle::fromRadians(M_PI * 5);
     * $alpha->wrap();
     */
    public function wrap(bool $signed = true): self
    {
        // Wrap the angle.
        $this->radians = self::wrapRadians($this->radians, $signed);

        // Return $this for chaining.
        return $this;
    }

    // endregion

    // endregion

    // region String-related methods

    /**
     * Format a float with an optional number of decimal places.
     *
     * NB: This is a private method called from format().
     * It will not throw an exception on invalid input, as the arguments are assumed to be already validated in calling
     * methods.
     *
     * @param float $value The value to format.
     * @param ?int $decimals Number of decimal places to show, or null for the maximum (with no trailing zeros).
     * @return string The formatted string.
     */
    private static function formatFloat(float $value, ?int $decimals = null): string
    {
        // Canonicalize -0.0 to 0.0.
        $value = Floats::normalizeZero($value);

        // If the number of decimal places is specified, format with that many decimal places.
        // If the number of decimal places isn't specified, use the max float precision, then trim off any trailing
        // 0's or decimal point.
        return $decimals !== null
            ? sprintf("%.{$decimals}F", $value)
            : rtrim(sprintf('%.17F', $value), '.0');
    }

    /**
     * Format angle given in degrees, arcminutes, and arcseconds as "D° M′ S″".
     *
     * For the $smallest_unit parameter, you can use the UNIT class constants, i.e.
     * - UNIT_DEGREE for degrees only
     * - UNIT_ARCMINUTE for degrees and arcminutes
     * - UNIT_ARCSECOND for degrees, arcminutes, and arcseconds
     *
     * @param int $smallest_unit 0 for degrees (default), 1 for arcminutes, 2 for arcseconds.
     * @param ?int $decimals Optional number of decimal places for the smallest unit.
     * @return string The degrees, arcminutes, and arcseconds nicely formatted as a string.
     * @throws ValueError If the smallest unit argument is not 0, 1, or 2.
     */
    private function formatDMS(int $smallest_unit = self::UNIT_DEGREE, ?int $decimals = null): string
    {
        // Get the sign string.
        $sign = $this->radians < 0 ? '-' : '';

        // Convert to degrees, with optional arcminutes and/or arcseconds.
        $parts = $this->abs()->toDMS($smallest_unit);

        switch ($smallest_unit) {
            case self::UNIT_DEGREE:
                [$d] = $parts;
                $str_d = self::formatFloat($d, $decimals);
                return "$sign{$str_d}°";

            case self::UNIT_ARCMINUTE:
                [$d, $m] = $parts;

                // Round the smallest unit if requested.
                if ($decimals !== null) {
                    $m = round($m, $decimals);

                    // Handle floating-point drift and carry.
                    if ($m >= self::ARCMINUTES_PER_DEGREE) {
                        $m = 0.0;
                        $d += 1.0;
                    }
                }

                $str_m = self::formatFloat($m, $decimals);
                return "$sign{$d}° {$str_m}′";

            case self::UNIT_ARCSECOND:
                [$d, $m, $s] = $parts;

                // Round the smallest unit if requested.
                if ($decimals !== null) {
                    $s = round($s, $decimals);

                    // Handle floating-point drift and carry.
                    if ($s >= self::ARCSECONDS_PER_ARCMINUTE) {
                        $s = 0.0;
                        $m += 1.0;
                    }
                    if ($m >= self::ARCMINUTES_PER_DEGREE) {
                        $m = 0.0;
                        $d += 1.0;
                    }
                }

                $str_s = self::formatFloat($s, $decimals);
                return "$sign{$d}° {$m}′ {$str_s}″";

            // @codeCoverageIgnoreStart
            default:
                throw new ValueError(
                    'The smallest unit must be 0 for degrees, 1 for arcminutes, or 2 for arcseconds (default).'
                );
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Format the angle as a string.
     *
     *  Supported formats:
     *  - 'rad', 'deg', 'grad', 'turn'  => CSS-style numeric+unit (no space)
     *  - 'd'    => degrees only (°)
     *  - 'dm'   => degrees + minutes (° ′)
     *  - 'dms'  => degrees + minutes + seconds (° ′ ″)
     *
     * @param string $format A format string (case-insensitive).
     * @param ?int $decimals Optional number of decimal places for the value (or the smallest unit in DMS formats).
     * @return string The angle as a string.
     * @throws ValueError If $format is not one of the supported formats or if $decimals is negative.
     */
    public function format(string $format = 'rad', ?int $decimals = null): string
    {
        // Guard.
        if ($decimals !== null && $decimals < 0) {
            throw new ValueError('Decimals must be non-negative or null.');
        }

        return match (strtolower($format)) {
            'rad'   => self::formatFloat($this->toRadians(), $decimals) . 'rad',
            'deg'   => self::formatFloat($this->toDegrees(), $decimals) . 'deg',
            'grad'  => self::formatFloat($this->toGradians(), $decimals) . 'grad',
            'turn'  => self::formatFloat($this->toTurns(), $decimals) . 'turn',
            'd'     => $this->formatDMS(self::UNIT_DEGREE, $decimals),
            'dm'    => $this->formatDMS(self::UNIT_ARCMINUTE, $decimals),
            'dms'   => $this->formatDMS(self::UNIT_ARCSECOND, $decimals),
            default => throw new ValueError(
                'Invalid format string. Allowed: rad, deg, grad, turn, d, dm, dms.'
            ),
        };
    }

    /**
     * Return the angle as a string, showing the units in radians using CSS notation.
     *
     * @return string The angle as a string.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion
}
