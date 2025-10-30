<?php

declare(strict_types = 1);

namespace Galaxon\Core;

use ValueError;

/**
 * Container for general number-related utility methods.
 */
final class Numbers
{
    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Get the sign of a number.
     *
     * This method has two main forms of operation, both of which are reasonably common.
     * 1. The default behaviour is to return 1 for positive numbers, -1 for negative numbers, and 0 for zero.
     * 2. The second form, where $zeroForZero is set to false, will only return -1 or 1. If the primary argument is
     *    zero, it will return -1 for the special float value -0.0, or 1 for int 0 or float 0.0.
     *
     * @param int|float $value The number whose sign to check.
     * @param bool $zeroForZero If true (default), returns 0 if value is zero; otherwise, return the sign of the zero.
     * @return int 1 if the number is positive, -1 if negative, and 0, 1, or -1 if 0, depending on the second argument.
     */
    public static function sign(int|float $value, bool $zeroForZero = true): int
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
        if ($zeroForZero) {
            return 0;
        }

        // Return the sign of the zero.
        return is_float($value) && Floats::isNegativeZero($value) ? -1 : 1;
    }

    /**
     * Copy the sign of one number to another.
     *
     * @param int|float $num The number to copy the sign to.
     * @param int|float $sign_source The number to copy the sign from.
     * @return int|float The number with the sign of $sign_source.
     * @throws ValueError If NaN is passed as either parameter.
     */
    public static function copySign(int|float $num, int|float $sign_source): int|float
    {
        // Guard. This method won't work for NaN, which doesn't have a sign.
        if (is_nan($num) || is_nan($sign_source)) {
            throw new ValueError("NaN is not allowed for either parameter.");
        }

        return abs($num) * self::sign($sign_source, false);
    }
}
