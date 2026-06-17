# Strings

Static utility class for string operations.

---

## Overview

The `Strings` class provides methods for converting values to strings and outputting them. It bridges the gap between PHP's built-in string casting (which silently converts booleans and null to empty or partial strings) and `Stringify::stringify()` (which wraps strings in quotes for code-like output).

The key difference from `Stringify`:
- **Strings** passes strings through as-is and uses `__toString()` for Stringable objects. Non-string, non-Stringable values are converted via `Stringify::stringify()`.
- **Stringify** always produces a code-like representation (e.g. strings are wrapped in single quotes, booleans are `'true'`/`'false'`).

This makes `Strings` ideal for user-facing output, while `Stringify` is better for debugging and logging.

---

## Methods

### toString()

```php
public static function toString(mixed $value): string
```

Convert any value to a string. Strings pass through as-is. Stringable objects use `__toString()`. All other types are converted via `Stringify::stringify()`.

**Parameters:**
- `$value` (mixed) - The value to convert.

**Returns:**
- `string` - The string representation.

**Examples:**

```php
Strings::toString('hello');       // 'hello' (passed through)
Strings::toString(42);            // '42'
Strings::toString(true);          // 'true'
Strings::toString(false);         // 'false'
Strings::toString(null);          // 'null'
Strings::toString([1, 2, 3]);     // '[1, 2, 3]'

// Stringable objects use __toString()
$date = new DateTime('2026-03-31');
Strings::toString($date);         // '2026-03-31 00:00:00'
```

### print()

```php
public static function print(mixed $value): void
```

Print a value to stdout. Equivalent to `echo Strings::toString($value)`.

**Parameters:**
- `$value` (mixed) - The value to print.

**Example:**

```php
Strings::print('hello');  // Outputs: hello
Strings::print(42);       // Outputs: 42
```

### println()

```php
public static function println(mixed $value): void
```

Print a value to stdout followed by a newline. Equivalent to `echo Strings::toString($value), PHP_EOL`.

Also available as a plain function: `OceanMoon\Core\println()`.

**Parameters:**
- `$value` (mixed) - The value to print.

**Example:**

```php
Strings::println('hello');  // Outputs: hello\n
Strings::println(true);     // Outputs: true\n
```

---

## See Also

- **[Stringify](Stringify.md)** - Code-like value-to-string conversion (wraps strings in quotes, pretty-prints arrays)
- **[println()](Functions.md#println)** - convenience function (delegates to `Strings::println()`)
