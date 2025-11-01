<?php

declare(strict_types = 1);

namespace Galaxon\Core;

// Interfaces
use Stringable;

// Attributes
use Override;

// Throwables
use Throwable;
use ValueError;
use DivisionByZeroError;

class Angle implements Stringable
{
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
    private float $_radians;

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
            throw new ValueError("Angle size cannot be ±∞ or NaN.");
        }

        $this->_radians = $radians;
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
     * Create an angle from degrees.
     *
     * @param float $degrees The angle in degrees.
     * @return self The angle instance.
     * @throws ValueError If the argument is a non-finite number.
     */
    public static function fromDegrees(float $degrees): self
    {
        return new self($degrees / self::DEGREES_PER_RADIAN);
    }

    /**
     * Create an angle from degrees, arcminutes, and arcseconds.
     *
     * NB: In theory all parts SHOULD be either non-negative (i.e. 0 or positive) or non-positive (i.e. 0 or negative).
     * However, this is not enforced. Neither do any of the values have to be within a certain range (e.g. 0-60 for
     * minutes or seconds).
     *
     * So, for example, if you want to convert -12° 34′ 56″ to degrees, call dmsToDeg(-12, -34, -56)
     * If you want to convert -12° 56″ to degrees, call dmsToDeg(-12, 0, -56).
     *
     * @param float $degrees The degrees part.
     * @param float $minutes The arcminutes part.
     * @param float $seconds The arcseconds part.
     * @return self A new angle with a magnitude equal to the provided angle.
     * @throws ValueError If any of the arguments are non-finite numbers.
     */
    public static function fromDMS(float $degrees, float $minutes = 0.0, float $seconds = 0.0): self
    {
        // Compute the total degrees.
        $total_deg = $degrees + $minutes / self::ARCMINUTES_PER_DEGREE + $seconds / self::ARCSECONDS_PER_DEGREE;
        return self::fromDegrees($total_deg);
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
     * The format with degrees, minutes, and seconds, as produced by the toDMSString() method, is
     * also supported.
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

        // Check for the DMS pattern as returned by toDMSString().
        // That means optional minus, then optional degrees, minutes, seconds.
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
            return self::fromDMS($d, $m, $s);
        }

        // Check for units.
        if (preg_match("/^(-?$num)\s*(rad|deg|grad|turn)$/i", $value, $m)) {
            $num = (float)$m[1];
            return match (strtolower($m[2])) {
                'rad'  => self::fromRadians($num),
                'deg'  => self::fromDegrees($num),
                'grad' => self::fromGradians($num),
                'turn' => self::fromTurns($num),
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
     * @param string $value The input string to parse (supports deg, rad, grad, turn, or DMS format).
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
        return $this->_radians;
    }

    /**
     * Get the angle in degrees.
     *
     * @return float The angle in degrees.
     */
    public function toDegrees(): float
    {
        return $this->_radians * self::DEGREES_PER_RADIAN;
    }

    /**
     * Get the angle in degrees, minutes, and seconds.
     *
     * The values will be returned in an array of floats. Only the smallest unit will be decimal; any larger ones will
     * be whole numbers.
     *
     * If the angle is non-negative, the resulting values will all be non-negative.
     * If the angle is negative, the resulting values will all be zero or negative.
     *
     * For the $smallest_unit parameter, you can use the UNIT class constants, i.e.
     * - UNIT_DEGREE for degrees only
     * - UNIT_ARCMINUTE for degrees and arcminutes
     * - UNIT_ARCSECOND for degrees, arcminutes, and arcseconds
     *
     * @param int $smallest_unit 0 for degrees, 1 for arcminutes, 2 for arcseconds (default 2).
     * @return float[] Array of 1-3 floats representing the degrees, arcminutes, and arcseconds.
     * @throws ValueError If $smallest_unit is not 0, 1, or 2.
     */
    public function toDMS(int $smallest_unit = self::UNIT_ARCSECOND): array
    {
        $a = $this->toDegrees();
        $sign = Numbers::sign($a, false);
        $a = abs($a);

        switch ($smallest_unit) {
            case self::UNIT_DEGREE:
                $d = $a;

                // Apply sign and normalize -0.0 to 0.0 to avoid surprising string output.
                $d = Floats::normalizeZero($d * $sign);

                return [$d];

            case self::UNIT_ARCMINUTE:
                // Convert the total degrees to degrees and minutes (non-negative).
                $d = floor($a);
                $m = ($a - $d) * self::ARCMINUTES_PER_DEGREE;

                // Apply sign and normalize -0.0 to 0.0 to avoid surprising string output.
                $d = Floats::normalizeZero($d * $sign);
                $m = Floats::normalizeZero($m * $sign);

                return [$d, $m];

            case self::UNIT_ARCSECOND:
                // Convert the total degrees to degrees, minutes, and seconds (non-negative).
                $d = floor($a);
                $f_min = ($a - $d) * self::ARCMINUTES_PER_DEGREE;
                $m = floor($f_min);
                $s = ($f_min - $m) * self::ARCSECONDS_PER_ARCMINUTE;

                // Apply sign and normalize -0.0 to 0.0 to avoid surprising string output.
                $d = Floats::normalizeZero($d * $sign);
                $m = Floats::normalizeZero($m * $sign);
                $s = Floats::normalizeZero($s * $sign);

                return [$d, $m, $s];

            default:
                throw new ValueError("The smallest unit must be 0 for degrees, 1 for arcminutes, or 2 for arcseconds (default).");
        }
    }

    /**
     * Get the angle in gradians.
     *
     * @return float The angle in gradians.
     */
    public function toGradians(): float
    {
        return $this->_radians * self::GRADIANS_PER_RADIAN;
    }

    /**
     * Get the angle in turns.
     *
     * @return float The angle in turns.
     */
    public function toTurns(): float
    {
        return $this->_radians / self::RADIANS_PER_TURN;
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
        return new self($this->_radians + $other->_radians);
    }

    /**
     * Subtract another angle from this angle.
     *
     * @param self $other The angle to subtract.
     * @return self The difference as a new angle.
     */
    public function sub(self $other): self
    {
        return new self($this->_radians - $other->_radians);
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
            throw new ValueError("Multiplier cannot be ±∞ or NaN.");
        }

        return new self($this->_radians * $k);
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
            throw new DivisionByZeroError("Divisor cannot be 0.");
        }
        if (!is_finite($k)) {
            throw new ValueError("Divisor cannot be ±∞ or NaN.");
        }

        return new self(fdiv($this->_radians, $k));
    }

    /**
     * Get the absolute value of this angle.
     *
     * @return self A new angle with a non-negative magnitude.
     */
    public function abs(): self
    {
        return new self(abs($this->_radians));
    }

    // endregion

    // region Comparison methods

    /**
     * Compare this angle to another within a tolerance.
     *
     * Returns:
     *      0 if the two angles are equal (or close enough, within $eps)
     *     -1 if this angle is smaller than the other
     *      1 if this angle is larger than the other
     *
     * @param self $other The other angle to compare with.
     * @param float $eps The tolerance for equality.
     * @return int -1, 0, or 1
     * @throws ValueError If $eps is negative.
     */
    public function cmp(self $other, float $eps = self::RAD_EPSILON): int
    {
        // Ensure epsilon is finite and non-negative.
        if ($eps < 0 || !is_finite($eps)) {
            throw new ValueError("Epsilon must be finite and non-negative.");
        }

        // Compute minimal signed difference in [-π, π).
        $delta = self::wrapRadians($this->_radians - $other->_radians, true);

        // Check for equal or close enough.
        if (abs($delta) <= $eps) {
            return 0;
        }

        // Check for less than or greater than.
        return $delta < 0 ? -1 : 1;
    }

    /**
     * Checks if two angles are equal within a tolerance.
     *
     * @param self $other The other angle to compare with.
     * @param float $eps The tolerance for equality.
     * @return bool True if angles are equal within $eps; false otherwise.
     */
    public function eq(self $other, float $eps = self::RAD_EPSILON): bool
    {
        return $this->cmp($other, $eps) === 0;
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
        return sin($this->_radians);
    }

    /**
     * Cosine of the angle.
     *
     * @return float The cosine value.
     */
    public function cos(): float
    {
        return cos($this->_radians);
    }

    /**
     * Tangent of the angle.
     *
     * @return float The tangent value.
     */
    public function tan(): float
    {
        $s = sin($this->_radians);
        $c = cos($this->_radians);

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
     * @return float
     */
    public function sinh(): float
    {
        return sinh($this->_radians);
    }

    /**
     * Get the hyperbolic cosine of the angle.
     *
     * @return float
     */
    public function cosh(): float
    {
        return cosh($this->_radians);
    }

    /**
     * Get the hyperbolic tangent of the angle.
     *
     * @return float
     */
    public function tanh(): float
    {
        return tanh($this->_radians);
    }

    // endregion

    // region Wrap methods

    // region Static methods

    /**
     * Normalize a scalar angle value into a specified half-open interval.
     *
     * NB: This is a private method called from the public static wrap[Unit]() methods.
     *
     * If $signed is false (default), the range is [0, $units_per_turn).
     * If $signed is true, the range is [-$units_per_turn/2, $units_per_turn/2).
     *
     * @param float $value The value to wrap.
     * @param float $units_per_turn Units per full turn (e.g., τ for radians, 360 for degrees, 400 for gradians).
     * @param bool $signed Whether to return a signed range instead of the default positive range.
     * @return float The wrapped value.
     * @throws ValueError If the $value argument is non-finite.
     */
    private static function _wrap(float $value, float $units_per_turn, bool $signed = false): float
    {
        // Guard.
        if (!is_finite($value)) {
            throw new ValueError("Value must be finite.");
        }

        // Reduce using fmod to avoid large magnitudes.
        $r = fmod($value, $units_per_turn);

        // Get the range bounds.
        if ($signed) {
            $half = $units_per_turn / 2.0;
            $min = -$half;
            $max = $half;
        }
        else {
            $min = 0.0;
            $max = $units_per_turn;
        }

        // The value may be outside the range due to the sign of $value or the value of $signed.
        // Adjust accordingly.
        if ($r < $min) {
            $r += $units_per_turn;
        }
        elseif ($r >= $max) {
            $r -= $units_per_turn;
        }

        // Canonicalize -0.0 to 0.0.
        return Floats::normalizeZero($r);
    }

    /**
     * Normalize radians into [0, τ) or [-π, π).
     *
     * @param float $radians The angle in radians.
     * @param bool $signed Whether to return a signed range.
     * @return float The normalized angle in radians.
     */
    public static function wrapRadians(float $radians, bool $signed = false): float
    {
        return self::_wrap($radians, self::TAU, $signed);
    }

    /**
     * Normalize degrees into [0, 360) or [-180, 180).
     *
     * @param float $degrees The angle in degrees.
     * @param bool $signed Whether to return a signed range.
     * @return float The normalized angle in degrees.
     */
    public static function wrapDegrees(float $degrees, bool $signed = false): float
    {
        return self::_wrap($degrees, self::DEGREES_PER_TURN, $signed);
    }

    /**
     * Normalize gradians into [0, 400) or [-200, 200).
     *
     * @param float $gradians The angle in gradians.
     * @param bool $signed Whether to return a signed range.
     * @return float The normalized angle in gradians.
     */
    public static function wrapGradians(float $gradians, bool $signed = false): float
    {
        return self::_wrap($gradians, self::GRADIANS_PER_TURN, $signed);
    }

    // endregion

    // region Instance method

    /**
     * Normalize an angle to a specified range.
     *
     * NB: This is a mutating method.
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
    public function wrap(bool $signed = false): self
    {
        // Wrap the angle.
        $this->_radians = self::wrapRadians($this->_radians, $signed);

        // Return $this for chaining.
        return $this;
    }

    // endregion

    // endregion

    // region String-related methods

    /**
     * Format a float with an optional number of decimal places.
     *
     * NB: This is a private method called from format() and formatDMS(). It will not throw an exception on invalid
     * input, as the arguments are assumed to be already validated in calling methods.
     *
     * @param float $value The value to format.
     * @param ?int $decimals Number of decimal places to show, or null for the maximum (with no trailing zeros).
     * @return string The formatted string.
     */
    private static function _formatFloat(float $value, ?int $decimals = null): string
    {
        // Canonicalize -0.0 to 0.0 to avoid surprising string output.
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
     * @param int $smallest_unit 0 for degrees, 1 for arcminutes, 2 for arcseconds (default).
     * @param ?int $decimals Optional number of decimal places for the smallest unit.
     * @return string The degrees, arcminutes, and arcseconds nicely formatted as a string.
     * @throws ValueError If the smallest unit argument is not 0, 1, or 2.
     */
    private function _formatDMS(int $smallest_unit = self::UNIT_ARCSECOND, ?int $decimals = null): string
    {
        // Get the sign string.
        $sign = $this->_radians < 0 ? '-' : '';

        // Convert to degrees, with optional minutes and seconds.
        $parts = $this->abs()->toDMS($smallest_unit);

        switch ($smallest_unit) {
            case self::UNIT_DEGREE:
                [$d] = $parts;
                $str_d = self::_formatFloat($d, $decimals);
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

                $str_m = self::_formatFloat($m, $decimals);
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

                $str_s = self::_formatFloat($s, $decimals);
                return "$sign{$d}° {$m}′ {$str_s}″";

            // @codeCoverageIgnoreStart
            default:
                throw new ValueError("The smallest unit must be 0 for degrees, 1 for arcminutes, or 2 for arcseconds (default).");
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
            throw new ValueError("Decimals must be non-negative or null.");
        }

        return match (strtolower($format)) {
            'rad'   => self::_formatFloat($this->toRadians(), $decimals) . 'rad',
            'deg'   => self::_formatFloat($this->toDegrees(), $decimals) . 'deg',
            'grad'  => self::_formatFloat($this->toGradians(), $decimals) . 'grad',
            'turn'  => self::_formatFloat($this->toTurns(), $decimals) . 'turn',
            'd'     => $this->_formatDMS(self::UNIT_DEGREE, $decimals),
            'dm'    => $this->_formatDMS(self::UNIT_ARCMINUTE, $decimals),
            'dms'   => $this->_formatDMS(self::UNIT_ARCSECOND, $decimals),
            default => throw new ValueError(
                "Invalid format string. Allowed: rad, deg, grad, turn, d, dm, dms."
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
