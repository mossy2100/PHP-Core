<?php

/**
 * @file
 * Useful constants and functions that work better as global.
 */

declare(strict_types=1);

namespace OceanMoon\Core;

use ErrorException;
use Throwable;

/**
 * The circle constant tau τ (tau) = 2π. Equal to the number of radians in a circle.
 *
 * To use it without requiring the namespace every time, include the following line:
 * ```php
 * use const OceanMoon\Core\M_TAU;
 * ```
 */
const M_TAU = 2 * M_PI;

/**
 * The marker used by Arrays and Stringify to represent a circular reference.
 *
 * This is intended to match the recursion marker text ("*RECURSION*") used by the print_r() function.
 */
const RECURSION = '*RECURSION*';

/**
 * Print a value followed by a newline.
 *
 * If the value is not a string, it will be converted to a string automatically by PHP.
 * This can produce a notice or warning for some values (arrays, closures, objects that are not Stringable).
 *
 * The function name mimics Java, Scala, Swift, Rust, Go, Julia, etc., and aligns with PHP's print() construct.
 *
 * @param mixed $value [optional] The value to print.
 */
function println(mixed $value = ''): void
{
    print $value . PHP_EOL; // @phpstan-ignore binaryOp.invalid
}

/**
 * Prints a stringified value.
 *
 * This is an alternative to var_dump(), var_export(), and print_r(), with some advantages:
 * 1. Concise, readable format.
 * 2. Value's type is apparent without being given explicitly.
 * 3. Doesn't error for any type.
 * 4. Handles recursion.
 *
 * @param mixed $value The value to print.
 * @param bool $prettyPrint Whether to format the output with newlines.
 * @param bool $return If a value should be returned or printed.
 * @return ?string Either null if printed, or a string representing the value.
 */
function inspect(mixed $value, bool $prettyPrint = false, bool $return = false): ?string
{
    // Stringify the argument.
    $str = Stringify::stringify($value, $prettyPrint);

    // If returning, return it.
    if ($return) {
        return $str;
    }

    // Otherwise, print it.
    println($str);
    return null;
}

/**
 * Gets a short string representation of a value suitable for an exception message.
 *
 * The max length is currently hard-coded to 32 (the default value in the wrapped Stringify::abbrev() method), which is
 * probably ok for now.
 *
 * @param mixed $value The value to convert to a string.
 * @return string The string representation of the value.
 */
function ex(mixed $value): string
{
    return Stringify::abbrev($value);
}

/**
 * Convert any value to a string, without errors.
 *
 * - Default string conversion is used when it works.
 * - Falls back to stringify() for values that emit warnings or errors on default string conversion (e.g. arrays, NAN, \
 *   non-Stringable objects).
 *
 * @param mixed $value Whatever you want converted to a string.
 * @return string The value as a string.
 */
function to_string(mixed $value): string
{
    // Temporarily convert warnings to exceptions to catch cases where the default cast would emit a warning.
    set_error_handler(static fn () => throw new ErrorException());

    try {
        return (string) $value; // @phpstan-ignore cast.string
    } catch (Throwable) {
        // Fall through to the DateTimeInterface/Stringify handling below.
    } finally {
        restore_error_handler();
    }

    // Fallback to stringify() which will handle anything else.
    return Stringify::stringify($value);
}

/**
 * Print a horizontal rule: a line of repeated characters followed by a newline.
 *
 * @param string $ch The character to repeat.
 * @param int $length The number of times to repeat it.
 */
function hr(string $ch = '-', int $length = 80): void
{
    println(str_repeat($ch, $length));
}
