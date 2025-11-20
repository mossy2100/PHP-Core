<?php

declare(strict_types=1);

namespace Galaxon\Core;

use ValueError;

/**
 * Container for general number-related utility methods.
 */
final class Numbers
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
     * Copy the sign of one number to another.
     *
     * @param int|float $num The number whose magnitude to use.
     * @param int|float $sign_source The number whose sign to copy.
     * @return int|float The magnitude of $num with the sign of $sign_source.
     * @throws ValueError If NaN is passed as either parameter.
     */
    public static function copySign(int|float $num, int|float $sign_source): int|float
    {
        // Guard. This method won't work for NaN, which doesn't have a sign.
        if (is_nan($num) || is_nan($sign_source)) {
            throw new ValueError('NaN is not allowed for either parameter.');
        }

        return abs($num) * self::sign($sign_source, false);
    }

    /**
     * Get the sign of a number.
     *
     * This method has two modes of operation, determined by the $zero_for_zero parameter.
     * In either mode, the method will return 1 for positive numbers and -1 for negative numbers.
     * 1. The default mode (when $zero_for_zero is true) will return 0 when $value equals 0.
     * 2. The alternate mode (when $zero_for_zero is false) will return -1 for the special float value -0.0, or 1 for
     *    int 0 or float +0.0.
     *
     * @param int|float $value The number to check.
     * @param bool $zero_for_zero If true, return 0 when $value equals 0. If false, return 1 or -1, indicating the sign
     * of the zero.
     * @return int The sign of the $value argument (-1, 0, or 1).
     */
    public static function sign(int|float $value, bool $zero_for_zero = true): int
    {
        // Check for positive.
        if ($value > 0) {
            return 1;
        }

        // Check for negative.
        if ($value < 0) {
            return -1;
        }

        // Value is 0. Return the default result if requested.
        if ($zero_for_zero) {
            return 0;
        }

        // Return the sign of the zero.
        return is_float($value) && Floats::isNegativeZero($value) ? -1 : 1;
    }
}
