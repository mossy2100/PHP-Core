<?php

declare(strict_types=1);

namespace OceanMoon\Core;

use BadMethodCallException;
use DomainException;
use OceanMoon\Core\Exceptions\FormatException;
use OverflowException;

/**
 * Container for useful integer-related methods.
 */
final class Integers
{
    #region Constants

    /**
     * Unicode subscript characters for digits and minus sign.
     *
     * @var array<non-numeric-string, string>
     */
    public const array SUBSCRIPT_CHARACTERS = [
        '-' => "\u{208b}",
        '0' => "\u{2080}",
        '1' => "\u{2081}",
        '2' => "\u{2082}",
        '3' => "\u{2083}",
        '4' => "\u{2084}",
        '5' => "\u{2085}",
        '6' => "\u{2086}",
        '7' => "\u{2087}",
        '8' => "\u{2088}",
        '9' => "\u{2089}",
    ];

    /**
     * Unicode superscript characters for digits and minus sign.
     *
     * @var array<non-numeric-string, string>
     */
    public const array SUPERSCRIPT_CHARACTERS = [
        '-' => "\u{207b}",
        '0' => "\u{2070}",
        '1' => "\u{00b9}",
        '2' => "\u{00b2}",
        '3' => "\u{00b3}",
        '4' => "\u{2074}",
        '5' => "\u{2075}",
        '6' => "\u{2076}",
        '7' => "\u{2077}",
        '8' => "\u{2078}",
        '9' => "\u{2079}",
    ];

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

    #region Binary arithmetic methods

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
            throw new OverflowException('Overflow in integer addition.');
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
            throw new OverflowException('Overflow in integer subtraction.');
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
            throw new OverflowException('Overflow in integer multiplication.');
        }

        // Return the result.
        return $c;
    }

    #endregion

    #region Power methods

    /**
     * Raise one integer to the power, to either produce an integer result or throw an exception.
     *
     * There are two possible reasons why the method could throw, i.e. the result is a float rather than an int:
     * - The exponent is negative, which produces a float result in all cases (including 1^-1 or -1^-1).
     * - The result of the operation is too large to be represented as an integer.
     *
     * @param int $a The base.
     * @param int $b The exponent (must be non-negative).
     * @return int The result of raising $a to the power of $b.
     * @throws DomainException If the exponent is negative.
     * @throws OverflowException If the result is too large to be represented as an integer.
     */
    public static function pow(int $a, int $b): int
    {
        // If the exponent is negative, throw an exception.
        // We know the result will be a float, so there's no need to do the operation.
        if ($b < 0) {
            throw new DomainException("Invalid exponent: $b. Must not be negative.");
        }

        // Do the exponentiation.
        $c = $a ** $b;

        // Check for overflow.
        if (is_float($c)) {
            throw new OverflowException('Overflow in integer exponentiation.');
        }

        // Return the result.
        return $c;
    }

    #endregion

    #region Number theory methods

    /**
     * Calculate the greatest common divisor of two or more integers.
     *
     * @param int ...$nums The integers to calculate the GCD of.
     * @return int The greatest common divisor.
     * @throws BadMethodCallException If no arguments are provided.
     * @throws OverflowException If the true result is PHP_INT_MIN's unsigned magnitude (2^63), which doesn't fit in
     *   an int. This only happens if PHP_INT_MIN is present and every other argument is 0 or also PHP_INT_MIN, since
     *   any other int's magnitude is at most 2^63 - 1, and would reduce the GCD below 2^63.
     */
    public static function gcd(int ...$nums): int
    {
        // At least one integer is required.
        if (count($nums) === 0) {
            throw new BadMethodCallException('At least one integer is required.');
        }

        // Run Euclid's algorithm on the raw (signed) values, without calling abs() on any intermediate value.
        // PHP_INT_MIN can't be negated without overflowing (its positive counterpart, 2^63, is out of int range),
        // but the modulo operator handles it fine: a remainder's magnitude is always smaller than the divisor's, so
        // it can never itself be PHP_INT_MIN. This means abs() is only ever needed once, on the final result below.
        $result = array_shift($nums);
        foreach ($nums as $num) {
            $b = $num;
            while ($b !== 0) {
                $temp = $b;
                $b = $result % $b;
                $result = $temp;
            }
        }

        // The only way the final result can still be PHP_INT_MIN is if nothing above ever reduced it, i.e. every
        // other argument was 0 (which leaves a value unchanged, per gcd(a, 0) = a) or PHP_INT_MIN itself. In that
        // case the true GCD is PHP_INT_MIN's magnitude, 2^63, which doesn't fit in an int.
        if ($result === PHP_INT_MIN) {
            throw new OverflowException('Cannot compute GCD. Result (2^63) exceeds the range of an int.');
        }

        return abs($result);
    }

    #endregion

    #region Conversion methods

    /**
     * Convert an integer to Unicode subscript characters.
     *
     * @param int $n The integer to convert.
     * @return string The integer as subscript characters (e.g., 123 → ₁₂₃).
     */
    public static function toSubscript(int $n): string
    {
        $s = (string) $n;
        $len = strlen($s);
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= self::SUBSCRIPT_CHARACTERS[$s[$i]];
        }
        return $result;
    }

    /**
     * Convert an integer to Unicode superscript characters.
     *
     * @param int $n The integer to convert.
     * @return string The integer as superscript characters (e.g., 123 → ¹²³).
     */
    public static function toSuperscript(int $n): string
    {
        $s = (string) $n;
        $len = strlen($s);
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= self::SUPERSCRIPT_CHARACTERS[$s[$i]];
        }
        return $result;
    }

    /**
     * Check if a string is a valid subscript integer representation.
     *
     * @param string $s The string to check.
     * @return bool True if the string matches the pattern for a subscript integer (e.g., ₁₂₃, ₋₅).
     */
    public static function isSubscript(string $s): bool
    {
        $minus = self::SUBSCRIPT_CHARACTERS['-'];
        $digits = implode('', array_slice(self::SUBSCRIPT_CHARACTERS, 1));
        return (bool) preg_match("/^$minus?[$digits]+$/u", $s);
    }

    /**
     * Check if a string is a valid superscript integer representation.
     *
     * @param string $s The string to check.
     * @return bool True if the string matches the pattern for a superscript integer (e.g., ¹²³, ⁻⁵).
     */
    public static function isSuperscript(string $s): bool
    {
        $minus = self::SUPERSCRIPT_CHARACTERS['-'];
        $digits = implode('', array_slice(self::SUPERSCRIPT_CHARACTERS, 1));
        return (bool) preg_match("/^$minus?[$digits]+$/u", $s);
    }

    /**
     * Convert a string of Unicode subscript characters to an integer.
     *
     * @param string $s The subscript string to convert (e.g., ₁₂₃ → 123, ₋₅ → -5).
     * @return int The integer value.
     * @throws FormatException If the string contains invalid subscript characters.
     */
    public static function fromSubscript(string $s): int
    {
        // Create reverse mapping.
        static $reverseMap = null;
        if ($reverseMap === null) {
            $reverseMap = array_flip(self::SUBSCRIPT_CHARACTERS);
        }

        // Convert each character.
        $result = '';
        $chars = mb_str_split($s);
        foreach ($chars as $char) {
            if (!isset($reverseMap[$char])) {
                throw new FormatException("Invalid subscript character: $char.");
            }
            $result .= $reverseMap[$char];
        }

        return (int) $result;
    }

    /**
     * Convert a string of Unicode superscript characters to an integer.
     *
     * @param string $s The superscript string to convert (e.g., ¹²³ → 123, ⁻⁵ → -5).
     * @return int The integer value.
     * @throws FormatException If the string contains invalid superscript characters.
     */
    public static function fromSuperscript(string $s): int
    {
        // Create reverse mapping.
        static $reverseMap = null;
        if ($reverseMap === null) {
            $reverseMap = array_flip(self::SUPERSCRIPT_CHARACTERS);
        }

        // Convert each character.
        $result = '';
        $chars = mb_str_split($s);
        foreach ($chars as $char) {
            if (!isset($reverseMap[$char])) {
                throw new FormatException("Invalid superscript character: $char.");
            }
            $result .= $reverseMap[$char];
        }

        return (int) $result;
    }

    #endregion
}
