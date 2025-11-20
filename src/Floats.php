<?php

declare(strict_types=1);

namespace Galaxon\Core;

use ArithmeticError;
use Random\RandomException;
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
     * The advantage of this method is that every possible float value will produce a unique 16-character hex string.
     * Whereas, with a cast to string or sprintf() the same string could be produced for different values.
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
     * @param ?int $i The equivalent integer.
     * @return bool True if the float can be converted to an integer losslessly, false otherwise.
     */
    public static function tryConvertToInt(float $f, ?int &$i): bool
    {
        // Check the provided value is finite.
        if (!is_finite($f)) {
            return false;
        }

        // Check if the argument is a float that can be converted losslessly to an integer.
        $temp = (int)$f;
        if ($f === (float)$temp) {
            $i = $temp;
            return true;
        }

        // Argument is a float that cannot be losslessly converted to an integer.
        return false;
    }

    /**
     * Returns the next floating-point number after the given one.
     *
     * @param float $f The given number.
     * @return float The next floating-point number after the given number.
     */
    public static function next(float $f): float
    {
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
     */
    public static function previous(float $f): float
    {
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
     * Generate a random finite float.
     *
     * @return float A random finite float (excludes NaN, ±INF, -0.0).
     * @throws RandomException If an appropriate source of randomness is unavailable.
     */
    public static function rand(): float
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
     * Generate a random float in the specified range.
     *
     * Not every possible float within the given range may be returnable from this method,
     * given that mt_rand() can only return 2^31 distinct values.
     *
     * @param float $min The minimum value (inclusive).
     * @param float $max The maximum value (inclusive).
     * @return float A random float in the range [min, max].
     * @throws ValueError If min or max are special values, or if min > max.
     */
    public static function randInRange(float $min, float $max): float
    {
        // Validate parameters.
        if (self::isSpecial($min) || self::isSpecial($max)) {
            throw new ValueError('Min and max must be finite, normal floats.');
        }
        if ($min > $max) {
            throw new ValueError('Min must be less than or equal to max.');
        }

        // Uniform float in [min, max].
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }
}
