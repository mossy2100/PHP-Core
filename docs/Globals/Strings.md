# Strings

Convenient functions for converting values to strings and printing or inspecting values.

---

## Overview

`src/Globals/strings.php` provides a small set of functions — namespaced under `OceanMoon\Core\Globals` — for
converting values to strings and printing them, mostly for debugging purposes. They provide a more useful output than
PHP's own `var_dump()`, `print_r()`, `var_export()`, or a plain `(string)` cast, none of which handle every PHP type
gracefully (arrays and non-`Stringable` objects can't be cast to string at all; `var_dump()`/`print_r()` are verbose
and don't distinguish types as clearly).

---

## Autoloading

Since these are functions rather than classes, PSR-4 autoloading won't discover them automatically. The Core
package's `composer.json` includes a `files` autoload entry covering all of `src/Globals/`:

```json
"autoload": {
    "psr-4": {
        "OceanMoon\\Core\\": "src/"
    },
    "files": [
        "src/Globals/constants.php",
        "src/Globals/strings.php",
        "src/Globals/numbers.php"
    ]
}
```

This means the functions are loaded automatically in any project that requires `oceanmoon/core`. To use them without
qualifying the namespace every time, add a `use function` import:

```php
use function OceanMoon\Core\Globals\write;
use function OceanMoon\Core\Globals\writeln;
```

---

## Functions

### println()

```php
function println(mixed $value = ''): void
```

Print a value followed by a newline. If the value is not a string, it's converted automatically by PHP — which can
produce a notice or warning for some values (arrays, closures, objects that aren't `Stringable`). The name mimics
Java, Scala, Swift, Rust, Go, Julia, etc., and aligns with PHP's `print()` construct.

Provided for completeness, but `writeln()` is generally the better choice — it never warns or throws, regardless of
the value's type.

**Parameters:**

- `$value` (mixed, optional) - The value to print. Defaults to `''`.

**Example:**

```php
use function OceanMoon\Core\Globals\println;

println('Hello, world!');  // Outputs: Hello, world!\n
```

### dump_var()

```php
function dump_var(mixed $value, bool $prettyPrint = false): void
```

Print a stringified value, using `Stringify::stringify()`. An alternative to `var_dump()`, `var_export()`, and
`print_r()`: the value's type is apparent without being given explicitly, output is concise, it never errors, and it
handles circular references.

**Parameters:**

- `$value` (mixed) - The value to print.
- `$prettyPrint` (bool) - Whether to format the output with newlines. Defaults to `false`.

**Example:**

```php
use function OceanMoon\Core\Globals\dump_var;

dump_var(['name' => 'John', 'age' => 30]);
// Outputs: ["name" => "John", "age" => 30]
```

### to_string()

```php
function to_string(mixed $value): string
```

Convert any value to a string, without errors or warnings.

**Behavior:**

1. Tries PHP's default `(string)` cast first, for any value except an array (casting an array to string only emits a
   warning rather than throwing, so it can't be caught here and is skipped up front).
2. If the value is a `DateTimeInterface` and the cast above didn't apply or failed (`DateTime`/`DateTimeImmutable`
   don't implement `Stringable`, so casting them throws), formats it as ISO 8601 via
   `DateTimeInterface::format(DateTimeInterface::ATOM)`.
3. Otherwise, falls back to `Stringify::stringify()` — this handles arrays, non-`Stringable` objects, resources, and
   anything else the cast above couldn't.

**Parameters:**

- `$value` (mixed) - The value to convert.

**Returns:**

- `string` - The value as a string.

**Examples:**

```php
use function OceanMoon\Core\Globals\to_string;

to_string('hello');                          // 'hello'
to_string(42);                                // '42'
to_string(true);                              // '1'
to_string(null);                              // ''
to_string(new DateTime('2026-07-17T12:00:00+00:00'));  // '2026-07-17T12:00:00+00:00'
to_string([1, 2, 3]);                         // '[1, 2, 3]' (via Stringify)
```

### write()

```php
function write(mixed $value): void
```

Print a value converted to a string using `to_string()`, with no trailing newline. Unlike `echo`/`print`, never
throws or warns regardless of the value's type.

**Parameters:**

- `$value` (mixed) - The value to print.

**Example:**

```php
use function OceanMoon\Core\Globals\write;

write('Hello, world!');  // Outputs: Hello, world!
```

### writeln()

```php
function writeln(mixed $value): void
```

Like `write()`, but appends `PHP_EOL` afterwards.

**Parameters:**

- `$value` (mixed) - The value to print.

**Example:**

```php
use function OceanMoon\Core\Globals\writeln;

writeln('Hello, world!');  // Outputs: Hello, world!\n
```

---

## See Also

- **[Numbers](Numbers.md)** - Number-related functions, including `is_number()`
- **[Constants](Constants.md)** - Shared constants, including `RECURSION`
- **[Stringify](../Stringify.md)** - Value-to-string conversion used internally by `dump_var()`, `to_string()`, and
  `write()`/`writeln()`
