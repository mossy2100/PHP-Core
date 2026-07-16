# Strings

Convenience functions in the `OceanMoon\Core` namespace that work better as plain functions than static methods.

---

## Overview

The `functions.php` file provides a small set of utility functions that are more natural to call as plain functions than
as static class methods. These are namespaced under `OceanMoon\Core`.

---

## Autoloading

Since these are functions rather than classes, PSR-4 autoloading won't discover them automatically. The Core package's
`composer.json` includes a `files` autoload entry:

```json
"autoload": {
    "psr-4": {
        "OceanMoon\\Core\\": "src/"
    },
    "files": [
        "src/functions.php"
    ]
}
```

This means the functions are loaded automatically in any project that requires `oceanmoon/core`. To use them, add a
`use function` import:

```php
use function OceanMoon\Core\write;
use function OceanMoon\Core\writeln;
```

---

## Functions

### write()

```php
function write(mixed $value = ''): void
```

Write a value to stdout, with no trailing newline. Strings are output as-is, `Stringable` objects use `__toString()`,
and all other types go through `Stringify::stringify()` -- see `Stringify::toString()`, which this delegates to.

**Parameters:**

- `$value` (mixed) - The value to print.

**Examples:**

```php
use function OceanMoon\Core\write;

write('Hello, world!');  // Outputs: Hello, world!
write(42);               // Outputs: 42
write(true);             // Outputs: true
write(null);             // Outputs: null
```

### writeln()

```php
function writeln(mixed $value = ''): void
```

Write a value to stdout followed by a newline. Behaves exactly like `write()`, but appends `PHP_EOL` afterwards.

**Parameters:**

- `$value` (mixed) - The value to print.

**Examples:**

```php
use function OceanMoon\Core\writeln;

writeln('Hello, world!');  // Outputs: Hello, world!\n
writeln(42);                // Outputs: 42\n
writeln(true);              // Outputs: true\n
writeln(null);              // Outputs: null\n
```

**Notes:**

- Uses `PHP_EOL` for the newline, so the line ending is platform-appropriate.

---

## See Also

- **[Numbers](Numbers.md)** - General number-related utility methods, including `isNumber()`
- **[Types](Types.md)** - Static utility class for type checking and inspection
- **[Stringify](Stringify.md)** - Value-to-string conversion used by `write()` and `writeln()` (via `toString()`)
