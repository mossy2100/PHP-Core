<?php

/**
 * @file
 * Convenient functions for converting values to strings and printing or inspecting values.
 * These are used mostly for debugging purposes, and can provide a more useful output than the usual var_dump() etc.
 */

declare(strict_types=1);

namespace OceanMoon\Core\Globals;

use OceanMoon\Core\Stringify;
use Throwable;

/**
 * Print a value followed by a newline.
 *
 * If the value is not a string, it will be converted to a string automatically by PHP.
 * This can produce a notice or warning for some values (arrays, closures, objects that are not Stringable).
 *
 * The function name mimics Java, Scala, Swift, Rust, Go, Julia, etc., and aligns with PHP's print() construct.
 *
 * Provided here for completeness, but writeln() is generally better.
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
 * 1. Value's type is apparent without being given explicitly.
 * 2. Concise format.
 * 3. Won't error.
 * 4. Handles recursion.
 *
 * @param mixed $value The value to print.
 * @param bool $prettyPrint Whether to format the output with newlines.
 */
function dump_var(mixed $value, bool $prettyPrint = false): void
{
    println(Stringify::stringify($value, $prettyPrint));
}

/**
 * Convert any value to a string, without errors.
 *
 * - Default string conversion is used when it works.
 * - Falls back to stringify() for values that emit warnings or errors on default string conversion.
 *
 * @param mixed $value Whatever you want converted to a string.
 * @return string The value as a string.
 */
function to_string(mixed $value): string
{
    // Don't cast array to string, because it emits a warning ("Warning: Array to string conversion...") rather than
    // throwing an Error or Exception.
    if (!is_array($value)) {
        try {
            return (string) $value; // @phpstan-ignore cast.string
        } catch (Throwable) {
        }
    }

    return Stringify::stringify($value);
}

/**
 * Print a value converted to a string using the to_string() method, which, unlike echo or print, won't throw errors.
 *
 * @param mixed $value The value to print.
 */
function write(mixed $value): void
{
    print to_string($value);
}

/**
 * Like write(), but adds a newline.
 *
 * @param mixed $value The value to print.
 */
function writeln(mixed $value): void
{
    println(to_string($value));
}
