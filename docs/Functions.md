# Functions

Convenience functions in the `OceanMoon\Core` namespace that work better as plain functions than static methods.

---

## Overview

The `functions.php` file provides a small set of utility functions that are more natural to call as plain functions than as static class methods. These are namespaced under `OceanMoon\Core`.

---

## Autoloading

Since these are functions rather than classes, PSR-4 autoloading won't discover them automatically. The Core package's `composer.json` includes a `files` autoload entry:

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

This means the functions are loaded automatically in any project that requires `oceanmoon/core`. To use them, add a `use function` import:

```php
use function OceanMoon\Core\println;
```

---

## Functions

### println()

```php
function println(mixed $value): void
```

Print a value and append a newline character. Strings are output as-is, `Stringable` objects use `__toString()`, and all other types go through `Stringify::stringify()`.

**Parameters:**
- `$value` (mixed) - The value to print.

**Examples:**

```php
use function OceanMoon\Core\println;

println('Hello, world!');  // Outputs: Hello, world!\n
println(42);               // Outputs: 42\n
println(true);             // Outputs: true\n
println(null);             // Outputs: null\n
```

**Notes:**
- Uses `PHP_EOL` for the newline, so the line ending is platform-appropriate.

---

## See Also

- **[Numbers](Numbers.md)** - General number-related utility methods, including `isNumber()`
- **[Types](Types.md)** - Static utility class for type checking and inspection
- **[Stringify](Stringify.md)** - Value-to-string conversion used by `println()`
