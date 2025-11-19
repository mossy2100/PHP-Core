<?php

declare(strict_types=1);

namespace Galaxon\Core;

use ArgumentCountError;
use OverflowException;
use RangeException;
use ValueError;

/**
 * Container for useful integer-related methods.
 */
final class Integers
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
     * Add two integers with overflow check.
     *
     * @param int $a The first integer.
     * @param int $b The second integer.
     * @return int The sum of the two integers.
     * @throws OverflowException If the addition results in overflow.
     */
    public static function add(int $a, int $b): int
    {
        // Do the addition.
        $c = $a + $b;

        // Check for overflow.
        // NB: phpstan complains because it thinks $c is always an int, but it could be a float.
        // @phpstan-ignore function.impossibleType
        if (is_float($c)) {
            throw new OverflowException("Overflow in integer addition.");
        }

        // Return the result.
        return $c;
    }

    /**
     * Subtract one integer from another with overflow check.
     *
     * @param int $a The first integer.
     * @param int $b The second integer.
     * @return int The difference.
     * @throws OverflowException If the subtraction results in overflow.
     */
    public static function sub(int $a, int $b): int
    {
        // Do the subtraction.
        $c = $a - $b;

        // Check for overflow.
        // NB: phpstan complains because it thinks $c is always an int, but it could be a float.
        // @phpstan-ignore function.impossibleType
        if (is_float($c)) {
            throw new OverflowException("Overflow in integer subtraction.");
        }

        // Return the result.
        return $c;
    }

    /**
     * Multiply two integers with overflow check.
     *
     * @param int $a The first integer.
     * @param int $b The second integer.
     * @return int The product.
     * @throws OverflowException If the multiplication results in overflow.
     */
    public static function mul(int $a, int $b): int
    {
        // Do the multiplication.
        $c = $a * $b;

        // Check for overflow.
        // NB: phpstan complains because it thinks $c is always an int, but it could be a float.
        // @phpstan-ignore function.impossibleType
        if (is_float($c)) {
            throw new OverflowException("Overflow in integer multiplication.");
        }

        // Return the result.
        return $c;
    }

    /**
     * Raise one integer to the power of another with an overflow check.
     *
     * @param int $a The base.
     * @param int $b The exponent (must be non-negative).
     * @return int The result of raising a to the power of b.
     * @throws ValueError If $b is negative.
     * @throws OverflowException If the exponentiation results in overflow.
     */
    public static function pow(int $a, int $b): int
    {
        // Handle b < 0.
        if ($b < 0) {
            throw new ValueError("Negative exponents are not supported.");
        }

        // Do the exponentiation.
        $c = $a ** $b;

        // Check for overflow.
        if (is_float($c)) {
            throw new OverflowException("Overflow in exponentiation.");
        }

        // Return the result.
        return $c;
    }

    /**
     * Calculate the greatest common divisor of two or more integers.
     *
     * @param int ...$nums The integers to calculate the GCD of.
     * @return int The greatest common divisor.
     * @throws ArgumentCountError If no arguments are provided.
     * @throws RangeException If any of the integers equal PHP_INT_MIN.
     */
    public static function gcd(int ...$nums): int
    {
        // Check we have the right number of arguments.
        if (count($nums) === 0) {
            throw new ArgumentCountError("At least one integer is required.");
        }

        // Check none of the values equal PHP_INT_MIN because otherwise abs() will not work properly.
        $range_err = 'Arguments must be greater than PHP_INT_MIN (' . PHP_INT_MIN . ').';
        if ($nums[0] === PHP_INT_MIN) {
            throw new RangeException($range_err);
        }

        // Initialise to the first number.
        $result = abs($nums[0]);

        // Calculate the GCD using Euclid's algorithm.
        for ($i = 1, $n = count($nums); $i < $n; $i++) {
            // Check integer is in the valid range.
            if ($nums[$i] === PHP_INT_MIN) {
                throw new RangeException($range_err);
            }

            $a = $result;
            $b = abs($nums[$i]);

            while ($b !== 0) {
                $temp = $b;
                $b = $a % $b;
                $a = $temp;
            }

            $result = $a;
        }

        return $result;
    }
}
