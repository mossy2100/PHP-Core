<?php

/**
 * Convenience functions that work better as plain functions than methods.
 * These are used mostly for debugging purposes, and provide a more useful output than the usual var_dump() etc.
 */

declare(strict_types=1);

namespace OceanMoon\Core;

/**
 * Write a value to stdout.
 *
 * @param mixed $value The value to print.
 */
function write(mixed $value = ''): void
{
    echo Stringify::toString($value);
}

/**
 * Write a value to stdout followed by a newline.
 *
 * @param mixed $value The value to print.
 */
function writeln(mixed $value = ''): void
{
    echo Stringify::toString($value), PHP_EOL;
}
