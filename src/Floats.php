<?php

declare(strict_types=1);

namespace OceanMoon\Core;

use DomainException;
use Random\RandomException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Container for useful float-related methods.
 */
final class Floats
{
    #region Constants

    /**
     * The default relative tolerance used by approxEqual().
     */
    public const float DEFAULT_RELATIVE_TOLERANCE = 1e-9;

    /**
     * The default absolute tolerance used by approxEqual().
     */
    public const float DEFAULT_ABSOLUTE_TOLERANCE = PHP_FLOAT_EPSILON;

    /**
     * Maximum integer that can be safely represented as a float and round-tripped without collision (2^53 - 1).
     * Beyond this value, multiple consecutive integers can convert to the same float. See isSafeInt() for additional
     * explanation.
     *
     * Equivalent to JavaScript's Number.MAX_SAFE_INTEGER.
     */
    public const int MAX_SAFE_INT = 2 ** 53 - 1;

    #endregion

    #region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    #endregion

    #region Inspection methods

    /**
     * Determines if a floating-point number is negative zero (-0.0).
     *
     * In IEEE-754 floating-point arithmetic, negative zero is a distinct value from positive zero, though they compare
     * as equal. This method provides a way to distinguish between them.
     *
     * The method works by dividing 1.0 by the value. For negative zero, this division results in -INF.
     *
     * @param float $value The floating-point number to check.
     * @return bool True if the value is negative zero (-0.0), false otherwise.
     */
    public static function isNegativeZero(float $value): bool
    {
        // Using fdiv() to avoid a division by zero error.
        return $value === 0.0 && fdiv(1.0, $value) === -INF;
    }

    /**
     * Determines if a floating-point number is positive zero (+0.0).
     *
     * In IEEE-754 floating-point arithmetic, positive zero is a distinct value from negative zero, though they compare
     * as equal. This method provides a way to distinguish between them.
     *
     * The method works by dividing 1.0 by the value. For positive zero, this division results in INF.
     *
     * @param float $value The floating-point number to check.
     * @return bool True if the value is positive zero (+0.0), false otherwise.
     */
    public static function isPositiveZero(float $value): bool
    {
        // Using fdiv() to avoid a division by zero error.
        return $value === 0.0 && fdiv(1.0, $value) === INF;
    }

    /**
     * Check if a number is negative.
     *
     * This method returns:
     * - true for -0.0, -INF, and negative values
     * - false for +0.0, INF, NAN, and positive values
     *
     * @param float $value The value to check.
     * @return bool True if the value is negative, false otherwise.
     */
    public static function isNegative(float $value): bool
    {
        return !is_nan($value) && ($value < 0 || self::isNegativeZero($value));
    }

    /**
     * Check if a number is positive.
     *
     * This method returns:
     * - true for +0.0, INF, and positive values
     * - false for -0.0, -INF, NAN, and negative values
     *
     * @param float $value The value to check.
     * @return bool True if the value is positive, false otherwise.
     */
    public static function isPositive(float $value): bool
    {
        return !is_nan($value) && ($value > 0 || self::isPositiveZero($value));
    }

    /**
     * Check if a float is one of the special values: NAN, -0.0, +INF, -INF.
     * +0.0 is not considered a special value.
     *
     * @param float $value The value to check.
     * @return bool True if the value is a special value, false otherwise.
     */
    public static function isSpecial(float $value): bool
    {
        return !is_finite($value) || self::isNegativeZero($value);
    }

    #endregion

    #region Comparison methods

    /**
     * Check if two floats are approximately equal.
     *
     * The absolute tolerance is checked first (useful for comparisons near zero).
     * If exceeded, the relative tolerance is checked.
     *
     * To compare purely by absolute difference, set the relative tolerance to zero.
     * To compare purely by relative difference, set the absolute tolerance to zero.
     *
     * This method mimics Python math.isclose()
     * @see https://docs.python.org/3/library/math.html#math.isclose
     *
     * @param float $a The first float.
     * @param float $b The second float.
     * @param float $relTol The maximum allowed relative difference.
     * @param float $absTol The maximum allowed absolute difference.
     * @return bool True if the two floats are approximately equal, false otherwise.
     * @throws DomainException If either tolerance is negative.
     */
    public static function approxEqual(
        float $a,
        float $b,
        float $relTol = self::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = self::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        self::validateTolerances($relTol, $absTol);

        // Handle NAN. NAN != anything, even itself.
        if (is_nan($a) || is_nan($b)) {
            return false;
        }

        // Handle infinities. The tolerance calculations don't work for infinities, so we use exact equality.
        if (abs($a) === INF || abs($b) === INF) {
            return $a === $b;
        }

        // Exact equality (handles identical values).
        if ($a === $b) {
            return true;
        }

        // Calculate absolute difference.
        $diff = abs($a - $b);

        // Absolute tolerance for numbers near zero.
        if ($diff <= $absTol) {
            return true;
        }

        // Relative tolerance otherwise.
        return $diff <= $relTol * max(abs($a), abs($b));
    }

    /**
     * Compare two floats.
     * Returns -1 if $a < $b, 0 if approximately equal (within tolerance), and 1 if $a > $b.
     * To compare purely by absolute difference, set the relative tolerance to zero.
     *
     * @param float $a The first float.
     * @param float $b The second float.
     * @param float $relTol The maximum allowed relative difference.
     * @param float $absTol The maximum allowed absolute difference.
     * @return int -1 if $a < $b, 0 if $a == $b (within tolerance), 1 if $a > $b.
     * @throws DomainException If either tolerance is negative, or either float is NAN.
     * @see approxEqual()
     */
    public static function approxCompare(
        float $a,
        float $b,
        float $relTol = self::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = self::DEFAULT_ABSOLUTE_TOLERANCE
    ): int {
        self::validateTolerances($relTol, $absTol);

        // NAN doesn't compare as equal, less than, or greater than anything, including itself.
        if (is_nan($a) || is_nan($b)) {
            throw new DomainException('Cannot compare NAN with any other value, even itself.');
        }

        // If they are approximately equal, return 0, otherwise use the spaceship operator to get -1 or 1.
        return self::approxEqual($a, $b, $relTol, $absTol) ? 0 : sign($a <=> $b);
    }

    #endregion

    #region Transformation methods

    /**
     * Normalize negative zero to positive zero. This can be used to avoid surprising results from certain operations.
     *
     * @param float $value The floating-point number to normalize.
     * @return float The normalized floating-point number.
     */
    public static function normalizeZero(float $value): float
    {
        // The values +0.0 and -0.0 are considered equal by PHP's equality operators.
        return $value === 0.0 ? 0.0 : $value;
    }

    /**
     * Truncate a float towards zero (remove the fractional part).
     *
     * This is equivalent to floor() for positive numbers and ceil() for negative numbers.
     * Unlike casting to int, this method handles values outside PHP's integer range.
     *
     * For example:
     * - Floats::trunc(3.7) → 3.0
     * - Floats::trunc(-3.7) → -3.0
     * - Floats::trunc(3.0) → 3.0
     * - Floats::trunc(-0.5) → 0.0
     *
     * Special cases:
     * - Floats::trunc(NAN) returns NAN
     * - Floats::trunc(±INF) returns ±INF
     *
     * @param float $value The value to truncate.
     * @return float The truncated value.
     */
    public static function trunc(float $value): float
    {
        if (!is_finite($value)) {
            return $value;
        }
        return $value >= 0 ? floor($value) : ceil($value);
    }

    /**
     * Return the fractional part of a float.
     *
     * This method satisfies the identity x = Floats::trunc(x) + Floats::frac(x), even for non-finite numbers.
     *
     * For result will have the same sign as the input value. For example:
     * - Floats::frac(3.7) → 0.7
     * - Floats::frac(-3.7) → -0.7
     *
     * Special cases:
     * - Floats::frac(NAN) returns NAN
     * - Floats::frac(±INF) returns 0.0 (infinity has no fractional part)
     *
     * @param float $value The value to get the fractional part of.
     * @return float The fractional part.
     *
     * @see Floats::trunc() For the integer part towards zero
     */
    public static function frac(float $value): float
    {
        if (is_nan($value)) {
            return NAN;
        }
        if (is_infinite($value)) {
            return 0.0;
        }
        return $value - self::trunc($value);
    }

    /**
     * Wrap a value (typically an angle) to a standard range.
     *
     * The range of values varies depending on the $unitsPerTurn parameter *and* the $signed flag.
     *
     * 1. If $signed is true (default), the range is (-$unitsPerTurn/2, $unitsPerTurn/2]
     * This means the minimum value is *excluded* in the range, while the maximum value is *included*.
     * For radians, this is (-π, π]
     * For degrees, this is (-180, 180]
     *
     * 2. If $signed is false, the range is [0, $unitsPerTurn)
     * This means the minimum value is *included* in the range, while the maximum value is *excluded*.
     * For radians, this is [0, τ)
     * For degrees, this is [0, 360)
     *
     * @param float $value The value to wrap.
     * @param float $unitsPerTurn The number of units per full rotation (default TAU).
     * @param bool $signed If true (default), wrap to the signed range; otherwise wrap to the unsigned range.
     * @return float The wrapped value.
     */
    public static function wrap(float $value, float $unitsPerTurn = M_TAU, bool $signed = true): float
    {
        // Reduce using fmod to avoid large magnitudes.
        // $r will be in the range [0, $unitsPerTurn) if $value is positive, or (-$unitsPerTurn, 0] if negative.
        $r = fmod($value, $unitsPerTurn);

        // Adjust to fit within range bounds.
        // The value may be outside the range due to the sign of $value or the value of $signed.
        if ($signed) {
            // Signed range is (-$half, $half]
            $half = $unitsPerTurn / 2.0;
            if ($r <= -$half) {
                $r += $unitsPerTurn;
            } elseif ($r > $half) {
                $r -= $unitsPerTurn;
            }
        } else {
            // Unsigned range is [0, $unitsPerTurn)
            if ($r < 0.0) {
                $r += $unitsPerTurn;
            }
        }

        // Canonicalize -0.0 to 0.0.
        return self::normalizeZero($r);
    }

    #endregion

    #region Conversion methods

    /**
     * Convert a float to a hexadecimal string.
     *
     * The advantage of this method is that every possible float value will produce a unique 16-character hex string,
     * including special values.
     * Whereas, with a cast to string, or formatting with sprintf() or number_format(), the same string could be
     * produced for different values.
     *
     * NB: The method works for NAN, but technically NAN doesn't have a unique hex representation.
     * The method will return the hex representation of the canonical representation for NAN (0x7ff8000000000000)
     * used by PHP.
     *
     * @param float $value The float to convert.
     * @return string The hexadecimal string representation of the float.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function toHex(float $value): string
    {
        // Convert to bits and then to hex.
        return sprintf('%016x', self::floatToBits($value));
    }

    /**
     * Format a float as a string with control over precision and notation.
     *
     * Format specifiers:
     *   - 'e': Scientific notation with lowercase 'e'.
     *   - 'E': Scientific notation with uppercase 'E'.
     *   - 'f': Fixed-point notation (locale-aware).
     *   - 'F': Fixed-point notation (non-locale-aware, always uses '.' as decimal separator).
     *   - 'g': Shortest of 'e' or 'f' (lower-case 'e'/locale-aware). [default]
     *   - 'G': Shortest of 'E' or 'f' (upper-case 'E'/locale-aware).
     *   - 'h': Shortest of 'e' or 'F' (lower-case 'e'/non-locale-aware).
     *   - 'H': Shortest of 'E' or 'F' (upper-case 'E'/non-locale-aware).
     * For more information, see https://www.php.net/manual/en/function.sprintf.php
     *
     * The meaning of the precision argument depends on the format specifier.
     *   - For e/E/f/F, precision means the number of digits after the decimal point.
     *   - For g/G/h/H, precision means the number of significant digits.
     *
     * If $trimZeros is true, trailing zeros (and if necessary, a trailing decimal point) are automatically
     * removed. For a value string with an exponent, this applies only to the mantissa (the part before the 'e').
     * If $trimZeros is false, all digits are preserved.
     * If $trimZeros is null (default), the behavior will depend on whether precision was specified or not.
     * If $precision is null, zeros will be trimmed; if the $precision is specified, zeros will not be trimmed.
     *
     * When $ascii is false and scientific notation is used, the exponent is rendered as ×10 with
     * superscript digits (e.g. 1.50×10³) instead of e+3.
     *
     * @param float $value The numeric value to format.
     * @param string $specifier The format specifier (default 'g').
     * @param ?int $precision Number of decimal places for e/f (default null = 6), or significant digits for g/h
     * (default null = 7).
     * @param ?bool $trimZeros If trailing zeros should be trimmed (default null for auto).
     * @param bool $ascii If true, use ASCII e notation. If false (default), use ×10 with superscript exponents.
     * @return string The formatted value string.
     * @throws DomainException If the specifier or precision is invalid.
     */
    public static function format(
        float $value,
        string $specifier = 'g',
        ?int $precision = null,
        ?bool $trimZeros = null,
        bool $ascii = false
    ): string {
        // Validate the specifier.
        $validFormats = ['e', 'E', 'f', 'F', 'g', 'G', 'h', 'H'];
        if (!in_array($specifier, $validFormats, true)) {
            $formatsString = Arrays::toSerialList(Arrays::quoteValues($validFormats), 'or');
            throw new DomainException("Invalid format specifier: '$specifier'. Must be $formatsString.");
        }

        // Validate the precision.
        if ($precision !== null && ($precision < 0 || $precision > 17)) {
            throw new DomainException("Invalid precision: $precision. Must be between 0 and 17.");
        }

        // Set $trimZeros if not set.
        if ($trimZeros === null) {
            $trimZeros = $precision === null;
        }

        // Canonicalize -0.0 to 0.0.
        $value = self::normalizeZero($value);

        // Format with the desired precision and specifier.
        // If precision is null, default to 7 for g/G/h/H (matching %e's 7 significant digits) and 6
        // for e/E/f/F (matching sprintf's default decimal places). This makes 'g' genuinely "the
        // shorter of e and f at matching precision".
        $effectivePrecision = $precision
            ?? (in_array($specifier, ['g', 'G', 'h', 'H'], true) ? 7 : 6);
        $formatString = "%.$effectivePrecision$specifier";
        $valueStr = sprintf($formatString, $value);

        // Look for an 'e' or 'E'.
        $ePos = stripos($valueStr, 'e');

        // Check for fixed point format.
        if ($ePos === false) {
            // Trim zeros if requested.
            if ($trimZeros && str_contains($valueStr, '.')) {
                $valueStr = rtrim(rtrim($valueStr, '0'), '.');
            }

            return $valueStr;
        }

        // Disassemble the value string.
        $mantissa = substr($valueStr, 0, $ePos);
        $expSeparator = $valueStr[$ePos];
        $exp = substr($valueStr, $ePos + 1);

        // Trim zeros from the mantissa if requested.
        if ($trimZeros && str_contains($mantissa, '.')) {
            $mantissa = rtrim(rtrim($mantissa, '0'), '.');
        }

        // If we want Unicode format and there's an exponent, replace it with the Unicode version.
        if (!$ascii) {
            $expSeparator = '×10';
            $exp = Integers::toSuperscript((int) $exp);
        }

        // Reassemble the value string.
        return $mantissa . $expSeparator . $exp;
    }

    #endregion

    #region Integer methods

    /**
     * Check if the float represents an integral value (not a value of type int), i.e. it has no decimal part.
     *
     * @param float $value The value to check.
     * @return bool True if the value is numerically an integer, false otherwise.
     */
    public static function isInt(float $value): bool
    {
        return is_finite($value) && floor($value) === $value;
    }

    /**
     * Check if a float value is exactly representable as an integer, and safely round-trippable.
     *
     * Returns true for finite integers within IEEE-754 double's safe integer range (±(2^53 - 1)).
     * Beyond this range, consecutive integers cannot all be exactly represented as floats.
     * Equivalently, multiple consecutive integers will convert to the same float.
     *
     * Equivalent to JavaScript's Number.isSafeInteger().
     *
     * For example:
     * - isSafeInt(42.0) → true
     * - isSafeInt(42.5) → false (has fractional part)
     * - isSafeInt(9007199254740991.0) → true (2^53 - 1, MAX_SAFE_INT)
     * - isSafeInt(9007199254740992.0) → false (2^53, exceeds MAX_SAFE_INT even though still exactly representable)
     *
     * @param float $value The value to check.
     * @return bool True if the value represents a safe integer, false otherwise.
     *
     * @see toInt() Attempt lossless conversion to int
     * @see ulp() Calculate precision at a given magnitude
     */
    public static function isSafeInt(float $value): bool
    {
        return self::isInt($value) && abs($value) <= self::MAX_SAFE_INT;
    }

    /**
     * Check if a float value is approximately an integral value (within tolerance).
     *
     * Unlike isInt(), this method allows for small floating-point errors that may accumulate during calculations.
     *
     * For example:
     * - isApproxInt(3.0) → true
     * - isApproxInt(3.0000000001) → true (within default tolerance)
     * - isApproxInt(3.5) → false
     * - isApproxInt(log(1000, 10)) → true (result is approximately 3)
     *
     * @param float $value The value to check.
     * @param float $relTol The maximum allowed relative difference.
     * @param float $absTol The maximum allowed absolute difference.
     * @return bool True if the value is approximately an integer, false otherwise.
     *
     * @see isInt() For exact integer check without tolerance
     * @see approxEqual() For comparing two floats with tolerance
     */
    public static function isApproxInt(
        float $value,
        float $relTol = self::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = self::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        // Non-finite values are never approximately an integer.
        if (!is_finite($value)) {
            return false;
        }

        return self::approxEqual($value, round($value), $relTol, $absTol);
    }

    /**
     * Convert a float to an integer losslessly.
     *
     * @param float $f The float to convert to an integer.
     * @return int The equivalent integer.
     * @throws DomainException If the float cannot be converted to an int losslessly.
     */
    public static function toInt(float $f): int
    {
        // Because PHP_INT_MIN is a power of 2, it can be represented as a float exactly, whereas PHP_INT_MAX cannot.
        $limit = (float) PHP_INT_MIN;
        if (self::isInt($f) && $f >= $limit && $f < -$limit) {
            return (int) $f;
        }

        // Use ex() rather than string interpolation to avoid warning triggered by NAN/±INF.
        throw new DomainException('Cannot convert float ' . ex($f) . ' to an int losslessly.');
    }

    #endregion

    #region Random methods

    /**
     * Generate a random float in the specified range by constructing random IEEE-754 components.
     *
     * This method can return any representable float in the given range, unlike randUniform() which is limited by
     * mt_rand()'s 2^31 distinct values.
     * Also unlike randUniform(), the possible resulting values are not evenly distributed within the range, but will
     * increase in density closer to zero.
     *
     * The method works by:
     * 1. Determining valid sign bit values based on min/max
     * 2. Determining valid exponent range based on min/max
     * 3. Determining valid fraction range based on min/max
     * 4. Generating random components
     * 5. Assembling the result
     * 6. Looping until a valid float within the defined range is generated
     *
     * @param float $min The minimum value (inclusive).
     * @param float $max The maximum value (inclusive).
     * @return float A random float in the range [min, max]. Excludes NAN, ±INF, -0.0.
     * @throws DomainException If min or max are non-finite or min > max.
     * @throws RandomException If an appropriate source of randomness is unavailable.
     * @throws RuntimeException If the system is not a 64-bit system.
     *
     * @see randUniform() For uniformly distributed random floats (faster but limited precision)
     */
    public static function rand(float $min = -PHP_FLOAT_MAX, float $max = PHP_FLOAT_MAX): float
    {
        // Validate parameters.
        if (!is_finite($min)) {
            throw new DomainException('Invalid minimum: ' . ex($min) . '. Must be finite.');
        }
        if (!is_finite($max)) {
            throw new DomainException('Invalid maximum: ' . ex($max) . '. Must be finite.');
        }
        if ($min > $max) {
            throw new DomainException(
                'Invalid range: [' . ex($min) . ', ' . ex($max) . ']. Minimum must not exceed maximum.'
            );
        }

        // Accept negative zero arguments but normalize to positive zero.
        $min = self::normalizeZero($min);
        $max = self::normalizeZero($max);

        // Handle edge case where min equals max.
        if ($min === $max) {
            return $min;
        }

        // If the default range is specified, use a faster method.
        if ($min === -PHP_FLOAT_MAX && $max === PHP_FLOAT_MAX) {
            do {
                // Get 64 random bits.
                $bytes = random_bytes(8);

                // Unpack into a float.
                /** @var list<float> $unpacked */
                $unpacked = unpack('d', $bytes);
                $f = $unpacked[1];

                // Continue until we find a non-special value.
            } while (self::isSpecial($f));
            return $f;
        }

        // Disassemble min and max into their IEEE-754 components.
        $minParts = self::disassemble($min);
        $maxParts = self::disassemble($max);

        // Get the min and max exponents and fractions.
        $minExp = min($minParts['exponent'], $maxParts['exponent']);
        $maxExp = max($minParts['exponent'], $maxParts['exponent']);
        $minFrac = min($minParts['fraction'], $maxParts['fraction']);
        $maxFrac = max($minParts['fraction'], $maxParts['fraction']);

        // Cache these comparisons.
        $sameSign = $minParts['sign'] === $maxParts['sign'];
        $sameExp = $minParts['exponent'] === $maxParts['exponent'];

        // Generate random floats with constrained components until one falls in range.
        do {
            // Get the random sign and exponent.
            if ($sameSign) {
                $sign = $minParts['sign'];
                // If the signs are the same, we can constrain the random exponent range to save time.
                $exp = random_int($minExp, $maxExp);
            } else {
                $sign = random_int(0, 1);
                $exp = random_int(0, $maxExp);
            }

            // If the signs and exponents are the same, we can constrain the random fraction range to save time.
            $fraction = $sameSign && $sameExp ? random_int($minFrac, $maxFrac) : random_int(0, 0xFFFFFFFFFFFFF);

            // Convert components to float.
            $f = self::assemble($sign, $exp, $fraction);
        } while (self::isSpecial($f) || $f < $min || $f > $max);

        return $f;
    }

    /**
     * Generate a uniformly distributed random float in the specified range.
     *
     * Selects from equidistant values spanning the range. The step size is calculated to ensure each selected value
     * maps to a distinct representable float, avoiding duplicates while maintaining uniform spacing.
     *
     * @param float $min The minimum value (inclusive).
     * @param float $max The maximum value (inclusive).
     * @return float A random float in the range [min, max].
     * @throws DomainException If min or max are non-finite or min > max.
     * @throws RandomException If an appropriate source of randomness is unavailable.
     * @throws RuntimeException If the system is not a 64-bit system.
     * @see rand() For non-uniform distribution across all representable floats.
     */
    public static function randUniform(float $min, float $max): float
    {
        // Validate parameters.
        if (!is_finite($min)) {
            throw new DomainException('Invalid minimum: ' . ex($min) . '. Must be finite.');
        }
        if (!is_finite($max)) {
            throw new DomainException('Invalid maximum: ' . ex($max) . '. Must be finite.');
        }
        if ($min > $max) {
            throw new DomainException(
                'Invalid range: [' . ex($min) . ', ' . ex($max) . ']. Minimum must not exceed maximum.'
            );
        }

        // If min and max are the same, there's only one possible value.
        if ($min === $max) {
            return $min;
        }

        // Get ULP at the maximum magnitude to determine the minimum safe step size.
        $maxMagnitude = max(abs($min), abs($max));
        $ulp = self::ulp($maxMagnitude);

        // Calculate number of steps that ensures no collisions.
        $range = $max - $min;
        $nValues = (int) round($range / $ulp);

        // Generate uniform random value.
        $r = random_int(0, $nValues) / $nValues;
        return $min + $r * $range;
    }

    #endregion

    #region Bit methods

    /**
     * Converts a float to its 64-bit integer representation.
     *
     * @param float $f The float to convert.
     * @return int The 64-bit integer representation.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function floatToBits(float $f): int
    {
        Environment::require64Bit();

        // Pack as little-endian double, unpack as little-endian unsigned 64-bit int.
        $values = unpack('P', pack('e', $f));

        if ($values === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Error unpacking int.');
            // @codeCoverageIgnoreEnd
        }

        return $values[1];
    }

    /**
     * Converts a 64-bit integer to a float by reinterpreting its bit pattern.
     *
     * This method directly interprets the bit pattern of an integer as an IEEE 754
     * double-precision float. This is different from casting an integer to a float,
     * which attempts to preserve the integer's numeric value rather than its bit representation.
     *
     * Caveat re NAN:
     * The IEEE 754 standard supports 2^53 - 2 distinct bit patterns that represent
     * NAN values. While this method can construct floats from any of these bit patterns,
     * PHP normalizes all NAN values to a canonical representation (0x7ff8000000000000)
     * in subsequent operations.
     *
     * @param int $bits The 64-bit integer representing the desired bit pattern.
     * @return float The float with the specified bit pattern.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function bitsToFloat(int $bits): float
    {
        Environment::require64Bit();

        // Pack as an unsigned 64-bit little-endian int, unpack as a little-endian double.
        $values = unpack('e', pack('P', $bits));

        if ($values === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException('Error unpacking float.');
            // @codeCoverageIgnoreEnd
        }

        return $values[1];
    }

    /**
     * Disassemble a float into its IEEE-754 components.
     *
     * IEEE-754 double-precision format:
     * - Sign: 1 bit (0 = positive, 1 = negative)
     * - Exponent: 11 bits (biased by 1023)
     * - Fraction: 52 bits (implicit leading 1 for normalized numbers)
     *
     * @param float $f The float to disassemble.
     * @return array{bits: int, sign: int, exponent: int, fraction: int} The IEEE-754 components.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function disassemble(float $f): array
    {
        Environment::require64Bit();

        // Convert float to bits.
        $bits = self::floatToBits($f);

        // Extract components.
        return [
            'bits'     => $bits,
            'sign'     => ($bits >> 63) & 0x1,
            'exponent' => ($bits >> 52) & 0x7FF,
            'fraction' => $bits & 0xFFFFFFFFFFFFF,
        ];
    }

    /**
     * Assemble a float from its IEEE-754 components.
     *
     * @param int $sign The sign bit (0 = positive, 1 = negative).
     * @param int $exponent The 11-bit biased exponent (0-2047).
     * @param int $fraction The 52-bit fraction/mantissa.
     * @return float The assembled float.
     * @throws DomainException If any component is out of range.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function assemble(int $sign, int $exponent, int $fraction): float
    {
        Environment::require64Bit();

        // Validate components.
        if ($sign < 0 || $sign > 1) {
            throw new DomainException("Invalid sign: $sign. Must be 0 or 1.");
        }
        if ($exponent < 0 || $exponent > 2047) {
            throw new DomainException("Invalid exponent: $exponent. Must be in the range [0, 2047].");
        }
        if ($fraction < 0 || $fraction > 0xFFFFFFFFFFFFF) {
            throw new DomainException("Invalid fraction: $fraction. Must be in the range [0, 2^52 - 1].");
        }

        // Assemble the float: sign (1 bit) | exponent (11 bits) | fraction (52 bits)
        $bits = ($sign << 63) | ($exponent << 52) | $fraction;

        // Convert bits to float.
        return self::bitsToFloat($bits);
    }

    /**
     * Returns the next floating-point number after the given one.
     *
     * @param float $f The given number.
     * @return float The next floating-point number after the given number.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function next(float $f): float
    {
        Environment::require64Bit();

        // Handle special cases.
        if (is_nan($f)) {
            return NAN;
        }
        if ($f === PHP_FLOAT_MAX || $f === INF) {
            return INF;
        }
        if ($f === -INF) {
            return -PHP_FLOAT_MAX;
        }
        if (self::isNegativeZero($f)) {
            return 0.0;
        }

        $bits = self::floatToBits($f);
        $bits += $bits >= 0 ? 1 : -1;
        return self::bitsToFloat($bits);
    }

    /**
     * Returns the previous floating-point number before the given one.
     *
     * @param float $f The given number.
     * @return float The previous floating-point number before the given number.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function previous(float $f): float
    {
        Environment::require64Bit();

        // Handle special cases.
        if (is_nan($f)) {
            return NAN;
        }
        if ($f === -PHP_FLOAT_MAX || $f === -INF) {
            return -INF;
        }
        if ($f === INF) {
            return PHP_FLOAT_MAX;
        }
        if (self::isPositiveZero($f)) {
            return -0.0;
        }

        $bits = self::floatToBits($f);
        $bits += $bits >= 0 ? -1 : 1;
        return self::bitsToFloat($bits);
    }

    /**
     * Calculate the Unit in Last Place (ULP) - the spacing between adjacent floats.
     *
     * ULP represents the gap between a float and the next largest representable float at that magnitude.
     * Larger magnitude numbers have larger ULP values, reflecting reduced precision at larger scales.
     *
     * Special cases:
     * - ulp(NAN) returns NAN
     * - ulp(±INF) returns INF
     * - ulp(±0.0) returns the smallest positive subnormal (≈ 4.9e-324)
     * - ulp(-x) == ulp(x) for all non-NAN values
     *
     * @param float $value The value to calculate ULP for.
     * @return float The ULP spacing.
     * @throws RuntimeException If the system is not a 64-bit system.
     *
     * @see next() Get the next representable float
     * @see previous() Get the previous representable float
     */
    public static function ulp(float $value): float
    {
        // Handle NAN.
        if (is_nan($value)) {
            return NAN;
        }

        // Handle ±INF.
        if (!is_finite($value)) {
            return INF;
        }

        // Handle ordinary values.
        $abs = abs($value);
        return self::next($abs) - $abs;
    }

    #endregion

    #region Helper methods

    /**
     * Check tolerances are valid.
     *
     * @param float $relTol The relative tolerance.
     * @param float $absTol The absolute tolerance.
     */
    private static function validateTolerances(
        float $relTol = self::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = self::DEFAULT_ABSOLUTE_TOLERANCE
    ): void {
        if (!is_finite($relTol) || $relTol < 0) {
            throw new DomainException(
                'Invalid relative tolerance: ' . ex($relTol) . '. Must be finite and non-negative.'
            );
        }
        if (!is_finite($absTol) || $absTol < 0) {
            throw new DomainException(
                'Invalid absolute tolerance: ' . ex($absTol) . '. Must be finite and non-negative.'
            );
        }
    }

    #endregion
}
