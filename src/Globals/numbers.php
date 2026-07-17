<?php

/**
 * @file
 * Convenient functions for numbers and math.
 */

declare(strict_types=1);

namespace OceanMoon\Core\Globals;

use DomainException;
use OceanMoon\Core\Floats;

#region Inspection functions

/**
 * Check if a value is a number, i.e. an integer or a float.
 *
 * This varies from is_numeric(), which also returns true for numeric strings.
 *
 * @param mixed $value The value to check.
 * @return bool True if the value is a number, false otherwise.
 * @phpstan-assert-if-true int|float $value
 */
function is_number(mixed $value): bool
{
    return is_int($value) || is_float($value);
}

/**
 * Check if a number is zero. Returns true for integer 0 and float ±0.0.
 *
 * @param int|float $value
 * @return bool
 */
function is_zero(int|float $value): bool
{
    // Note that -0.0 compares as exactly equal to 0.0.
    return $value === 0 || $value === 0.0;
}

#endregion

#region Sign functions

/**
 * Get the sign of a number.
 *
 * This function has two modes of operation, determined by the $zeroForZero parameter.
 * In either mode, the function will return 1 for positive numbers and -1 for negative numbers.
 * 1. The default mode (when $zeroForZero is true) will return 0 when $value equals 0.
 * 2. The alternate mode (when $zeroForZero is false) will return -1 for the special float value -0.0, or 1 for
 *    int 0 or float +0.0.
 *
 * @param int|float $value The number to check.
 * @param bool $zeroForZero If true (default), return 0 when $value equals 0. If false, return 1 or -1, indicating
 * the sign of the zero.
 * @return int The sign of the $value argument (-1, 0, or 1).
 */
function sign(int|float $value, bool $zeroForZero = true): int
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
 * @param int|float $num The number whose magnitude to use.
 * @param int|float $signSource The number whose sign to copy.
 * @return int|float The magnitude of $num with the sign of $signSource.
 * @throws DomainException If NAN is passed as either parameter.
 */
function copy_sign(int|float $num, int|float $signSource): int|float
{
    // Guard. This function won't work for NAN, which doesn't have a sign.
    if (is_nan($num) || is_nan($signSource)) {
        throw new DomainException('Cannot copy sign to or from NAN.');
    }

    return abs($num) * sign($signSource, false);
}

#endregion
