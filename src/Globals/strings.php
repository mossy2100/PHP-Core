<?php

/**
 * @file
 * Convenient functions for converting values to strings and printing or inspecting values.
 * These are used mostly for debugging purposes, and can provide a more useful output than the usual var_dump() etc.
 */

declare(strict_types=1);

namespace OceanMoon\Core\Globals;

use DateTimeInterface;
use ErrorException;
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
 * 1. Concise, readable format.
 * 2. Value's type is apparent without being given explicitly.
 * 3. Doesn't error for any type.
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
 * - Special handling for datetimes, which don't provide a default conversion to string.
 * - Falls back to stringify() for values that emit warnings or errors on default string conversion.
 *
 * @param mixed $value Whatever you want converted to a string.
 * @return string The value as a string.
 */
function to_string(mixed $value): string
{
    // Temporarily convert warnings to exceptions, so we can catch cases where the default cast to string would
    // otherwise just emit a warning (e.g. arrays) or a coercion warning (e.g. NAN) rather than throwing.
    // Scoped to just the cast attempt via try/finally: if this stayed active during the Stringify::stringify()
    // fallback below, a warning from that call would become an uncaught exception, since there's no surrounding
    // catch to handle it there.
    set_error_handler(static function (int $severity, string $message): never {
        throw new ErrorException($message, 0, $severity);
    });
    try {
        return (string) $value; // @phpstan-ignore cast.string
    } catch (Throwable) {
        // Fall through to the DateTimeInterface/Stringify handling below.
    } finally {
        restore_error_handler();
    }

    // Special handling for datetimes, which don't have a default string conversion.
    if ($value instanceof DateTimeInterface) {
        return $value->format(DateTimeInterface::ATOM);
    }

    // Fallback to stringify() which will handle anything else.
    return Stringify::stringify($value);
}

/**
 * Print a value converted to a string using to_string().
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
