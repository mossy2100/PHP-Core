<?php

declare(strict_types=1);

namespace OceanMoon\Core;

use Stringable;

/**
 * Static utility class for string operations.
 */
final class Strings
{
    /**
     * Convert any value to a string.
     *
     * Strings pass through as-is. Stringable objects use __toString(). All other types are
     * converted via Stringify::stringify() (without pretty printing).
     *
     * @param mixed $value The value to convert.
     * @return string The string representation.
     */
    public static function toString(mixed $value): string
    {
        return is_string($value) || $value instanceof Stringable ? (string)$value : Stringify::stringify($value);
    }

    /**
     * Print a value to stdout.
     *
     * @param mixed $value The value to print.
     */
    public static function print(mixed $value): void
    {
        echo self::toString($value);
    }

    /**
     * Print a value to stdout followed by a newline.
     *
     * @param mixed $value The value to print.
     */
    public static function println(mixed $value): void
    {
        echo self::toString($value), PHP_EOL;
    }
}
