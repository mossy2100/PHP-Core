<?php

/**
 * Convenience functions that work better as plain functions than methods.
 */

declare(strict_types=1);

namespace OceanMoon\Core;

/**
 * Print a value and append a newline character.
 *
 * Strings are output as-is.
 * Objects with a __toString() method are cast to strings.
 * Otherwise, the value is converted to a string using Stringify::stringify() (no pretty printing).
 *
 * This method makes it easier to distinguish null, bool, int, float, and string values, and provides a nice output
 * for arrays, objects, enums, and resources.
 *
 * @param mixed $value The value to echo.
 */
function println(mixed $value = ''): void
{
    Strings::println($value);
}
