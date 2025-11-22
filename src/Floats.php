<?php

declare(strict_types=1);

namespace Galaxon\Core;

use Random\RandomException;
use RuntimeException;
use ValueError;

/**
 * Container for useful float-related methods.
 */
final class Floats
{
    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Check if two floats are approximately equal within a given epsilon.
     *
     * @param float $f1 The first float.
     * @param float $f2 The second float.
     * @param float $epsilon The maximum allowed difference between the two floats.
     * @return bool True if the two floats are approximately equal, false otherwise.
     * @throws ValueError If epsilon is negative.
     */
    public static function approxEqual(float $f1, float $f2, float $epsilon = 1e-10): bool
    {
        // Make sure epsilon is non-negative.
        if ($epsilon < 0) {
            throw new ValueError('Epsilon must be non-negative.');
        }

        // Compare absolute differences.
        return abs($f1 - $f2) <= $epsilon;
    }

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
     * Normalize negative zero to positive zero. This can be used to avoid surprising results from certain operations.
     *
     * @param float $value The floating-point number to normalize.
     * @return float The normalized floating-point number.
     */
    public static function normalizeZero(float $value): float
    {
        return self::isNegativeZero($value) ? 0.0 : $value;
    }

    /**
     * Check if a number is negative.
     *
     * This method returns:
     * - true for -0.0, -INF, and negative values
     * - false for +0.0, INF, NaN, and positive values
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
     * - false for -0.0, -INF, NaN, and negative values
     *
     * @param float $value The value to check.
     * @return bool True if the value is positive, false otherwise.
     */
    public static function isPositive(float $value): bool
    {
        return !is_nan($value) && ($value > 0 || self::isPositiveZero($value));
    }

    /**
     * Check if a float is one of the special values: NaN, -0.0, +INF, -INF.
     * +0.0 is not considered a special value.
     *
     * @param float $value The value to check.
     * @return bool True if the value is a special value, false otherwise.
     */
    public static function isSpecial(float $value): bool
    {
        return !is_finite($value) || self::isNegativeZero($value);
    }

    /**
     * Convert a float to a hexadecimal string.
     *
     * The advantage of this method is that every possible float value will produce a unique 16-character hex string,
     * including special values.
     * Whereas, with a cast to string, or formatting with sprintf() or number_format(), the same string could be
     * produced for different values.
     *
     * @param float $value The float to convert.
     * @return string The hexadecimal string representation of the float.
     */
    public static function toHex(float $value): string
    {
        return bin2hex(pack('d', $value));
    }

    /**
     * Try to convert a float to an integer losslessly.
     *
     * @param float $f The float to convert to an integer.
     * @return null|int The equivalent integer, or null if conversion would lose precision.
     */
    public static function tryConvertToInt(float $f): ?int
    {
        // Check the provided value is finite.
        if (!is_finite($f)) {
            return null;
        }

        // Check if the argument is a float that can be converted losslessly to an integer.
        $i = (int)$f;
        if ($f === (float)$i) {
            return $i;
        }

        // Argument is a float that cannot be losslessly converted to an integer.
        return null;
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
        self::check64bit();

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
        self::check64bit();

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
     * Check if the current system is a 64-bit system.
     *
     * @return void
     * @throws RuntimeException
     */
    private static function check64bit()
    {
        // Check if we're on a 32-bit system.
        if (PHP_INT_SIZE === 4) {
            throw new RuntimeException('This method is designed for 64-bit systems.'); // @codeCoverageIgnore
        }
    }

    /**
     * Converts a float to its 64-bit integer representation.
     *
     * @param float $f The float to convert.
     * @return int The 64-bit integer representation.
     */
    private static function floatToBits(float $f): int
    {
        $packed = pack('d', $f);
        /** @var int[] $bytes */
        $bytes = unpack('C*', $packed);

        $bits = 0;
        for ($i = 8; $i >= 1; $i--) {
            $bits = ($bits << 8) | $bytes[$i];
        }

        return $bits;
    }

    /**
     * Converts a 64-bit integer to its float representation.
     *
     * @param int $bits The 64-bit integer.
     * @return float The float representation.
     */
    private static function bitsToFloat(int $bits): float
    {
        $bytes = [];
        for ($i = 1; $i <= 8; $i++) {
            $bytes[$i] = $bits & 0xFF;
            $bits >>= 8;
        }

        $packed = pack('C*', ...$bytes);
        /** @var float[] $result */
        $result = unpack('d', $packed);

        return $result[1];
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
     * @return array{sign: int, exponent: int, fraction: int} The IEEE-754 components.
     * @throws RuntimeException If the system is not a 64-bit system.
     */
    public static function disassemble(float $f): array
    {
        self::check64bit();

        // Convert float to bits.
        $bits = self::floatToBits($f);

        // Extract components.
        return [
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
     * @throws RuntimeException If the system is not a 64-bit system.
     * @throws ValueError If any component is out of range.
     */
    public static function assemble(int $sign, int $exponent, int $fraction): float
    {
        self::check64bit();

        // Validate components.
        if ($sign < 0 || $sign > 1) {
            throw new ValueError('Sign must be 0 or 1.');
        }
        if ($exponent < 0 || $exponent > 2047) {
            throw new ValueError('Exponent must be in the range [0, 2047].');
        }
        if ($fraction < 0 || $fraction > 0xFFFFFFFFFFFFF) {
            throw new ValueError('Fraction must be in the range [0, 2^52 - 1].');
        }

        // Assemble the float: sign (1 bit) | exponent (11 bits) | fraction (52 bits)
        $bits = ($sign << 63) | ($exponent << 52) | $fraction;

        // Convert bits to float.
        return self::bitsToFloat($bits);
    }

    /**
     * Generate a random finite float.
     *
     * @return float A random float. Excludes NAN, ±INF, -0.0.
     * @throws RandomException If an appropriate source of randomness is unavailable.
     */
    private static function randFloat(): float
    {
        do {
            $bytes = random_bytes(8);
            /** @var float[] $unpacked */
            $unpacked = unpack('d', $bytes);
            $f = $unpacked[1];
        } while (self::isSpecial($f));
        return $f;
    }

    /**
     * Generate a random float in the specified range by constructing IEEE-754 components.
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
     * @throws ValueError If min or max are non-finite, or if min > max.
     */
    public static function rand(float $min = -PHP_FLOAT_MAX, float $max = PHP_FLOAT_MAX): float
    {
        // Validate parameters.
        if (!is_finite($min) || !is_finite($max)) {
            throw new ValueError('Min and max must be finite.');
        }
        if ($min > $max) {
            throw new ValueError('Min must be less than or equal to max.');
        }

        // Accept negative zero arguments but normalize to positive zero.
        $min = self::normalizeZero($min);
        $max = self::normalizeZero($max);

        // Handle edge case where min equals max.
        if ($min === $max) {
            return $min;
        }

        // If the default range is specified, use the faster method.
        if ($min === -PHP_FLOAT_MAX && $max === PHP_FLOAT_MAX) {
            return self::randFloat();
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
            if ($sameSign && $sameExp) {
                $fraction = random_int($minFrac, $maxFrac);
            } else {
                $fraction = random_int(0, 0xFFFFFFFFFFFFF);
            }

            // Convert components to float.
            $f = self::assemble($sign, $exp, $fraction);
        } while (self::isSpecial($f) || $f < $min || $f > $max);

        return $f;
    }

    /**
     * Generate a random float in the specified range.
     *
     * Not every possible float within the given range is returnable from this method, because mt_rand() can only
     * return 2^31 distinct values.
     *
     * The main benefit of this method over rand() is speed.
     * It may also be beneficial for certain use cases that the possible resulting values are evenly distributed within
     * the range.
     *
     * @param float $min The minimum value (inclusive).
     * @param float $max The maximum value (inclusive).
     * @return float A random float in the range [min, max].
     * @throws ValueError If min or max are non-finite, or if min > max.
     */
    public static function randUniform(float $min, float $max): float
    {
        // Validate parameters.
        if (!is_finite($min) || !is_finite($max)) {
            throw new ValueError('Min and max must be finite.');
        }
        if ($min > $max) {
            throw new ValueError('Min must be less than or equal to max.');
        }

        // Uniform float in [min, max].
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }
}
