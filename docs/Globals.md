# Constants

Shared constants and functions used by Core, Math, and other packages.

---

## Overview

`src/globals.php` provides a small set of namespaced constants and functions (`OceanMoon\Core`) that don't belong to a
single class, but which are more usable as global identifiers. The number of items is deliberately kept small.

---

## Autoloading

Since these are namespaced global identifiers rather than class members, PSR-4 autoloading won't discover them
automatically. The Core package's `composer.json` includes a `files` autoload entry that loads `globals.php`.

To use a constant without qualifying the namespace every time, add a `use const` import:

**Example:**

```php
use const OceanMoon\Core\M_TAU;
```

To use a function without qualifying the namespace every time, add a `use function` import:

**Example:**

```php
use function OceanMoon\Core\println;
```

---

## Constants

### M_TAU

```php
const M_TAU = 2 * M_PI;
```

The circle constant tau τ = 2π. Equal to the number of radians in a full circle. Named to match PHP's own `M_PI`, `M_E`,
etc.

```php
use const OceanMoon\Core\M_TAU;

$fullCircleRadians = M_TAU;  // ≈ 6.283185307179586
```

### RECURSION

```php
const RECURSION = '*RECURSION*';
```

The marker string used by `Arrays::removeRecursion()` and `Stringify` to represent a circular (self-referencing)
reference, in place of infinitely recursing. Intended to match the recursion marker text PHP's own `print_r()` function
uses.

```php
use const OceanMoon\Core\RECURSION;

$arr = ['x' => 1];
$arr['self'] = &$arr;

$cleaned = Arrays::removeRecursion($arr);
// $cleaned['self'] === RECURSION
```

---

## Functions

### println()

```php
function println(mixed $value = ''): void
```

Print a value followed by a newline. If the value is not a string, it's converted automatically by PHP — which can
produce a notice or warning for some values (arrays, closures, objects that aren't `Stringable`). The name mimics Java,
Scala, Swift, Rust, Go, Julia, etc., and aligns with PHP's `print()` construct.

Provided for completeness, but `writeln()` is generally the better choice — it never warns or throws, regardless of the
value's type.

**Parameters:**

- `$value` (mixed, optional) - The value to print. Defaults to `''`.

**Example:**

```php
use function OceanMoon\Core\println;

println('Hello, world!');  // Outputs: Hello, world!\n
```

### inspect()

```php
function inspect(mixed $value, bool $prettyPrint = false, bool $return = false): ?string
```

Print a stringified value (see `Stringify::stringify()`). An alternative to `var_dump()`, `var_export()`, `print_r()`,
or a plain `(string)` cast, with a few advantages:

1. Output is concise, yet informative.
2. The value's type is apparent without necessarily given explicitly.
3. The function never errors, even with circular references.

**Parameters:**

- `$value` (mixed) - The value to print.
- `$prettyPrint` (bool) - Whether to format the output with newlines. Defaults to `false`.
- `$return` (bool) - If `true`, return the stringified value instead of printing it. Defaults to `false`.

**Returns:**

- `?string` - `null` if the value was printed, or the stringified value if `$return` is `true`.

**Example:**

```php
use function OceanMoon\Core\inspect;

inspect(['name' => 'John', 'age' => 30]);
// Outputs: ["name" => "John", "age" => 30]

$s = inspect(['name' => 'John', 'age' => 30], return: true);
// $s === '["name" => "John", "age" => 30]'
```

### to_string()

```php
function to_string(mixed $value): string
```

Convert any value to a string, without errors or warnings.

**Behavior:**

1. Tries PHP's default `(string)` cast first, with warnings temporarily promoted to exceptions so that cases which would
   otherwise just emit a warning (arrays) or a coercion warning (`NAN`) are caught here rather than escaping.
2. If the cast failed and the value is a `DateTimeInterface`, formats it as ISO 8601 via
   `DateTimeInterface::format(DateTimeInterface::ATOM)` (`DateTime`/`DateTimeImmutable` don't implement `Stringable`, so
   the cast above always throws for them).
3. Otherwise, falls back to `Stringify::stringify()` — this handles arrays, non-`Stringable` objects, resources, and
   anything else the cast above couldn't.

**Parameters:**

- `$value` (mixed) - The value to convert.

**Returns:**

- `string` - The value as a string.

**Examples:**

```php
use function OceanMoon\Core\to_string;

to_string('hello');                          // 'hello'
to_string(42);                                // '42'
to_string(true);                              // '1'
to_string(null);                              // ''
to_string(new DateTime('2026-07-17T12:00:00+00:00'));  // '2026-07-17T12:00:00+00:00'
to_string([1, 2, 3]);                         // '[1, 2, 3]' (via Stringify)
to_string(NAN);                               // 'NAN' (via Stringify; a direct cast would emit a warning)
```

### ex()

```php
function ex(mixed $value): string
```

Get a short, abbreviated string representation of a value, using `Stringify::abbrev()`. Intended for building exception
messages that report the invalid value without risking an overly long message for large arrays, strings, or objects.

The max length is hard-coded to 32 (`Stringify::abbrev()`'s own default) rather than exposed as a parameter.

**Parameters:**

- `$value` (mixed) - The value to convert to a string.

**Returns:**

- `string` - The abbreviated string representation of the value.

**Example:**

```php
use function OceanMoon\Core\ex;

throw new DomainException('Invalid minimum: ' . ex($min) . '. Must be finite.');
```

---

## See Also

- **[Numbers](Numbers.md)** - Number-related functions
- **[Arrays](Arrays.md)** - `removeRecursion()`, which uses the `RECURSION` marker
- **[Stringify](Stringify.md)** - Value-to-string conversion used internally by `inspect()`, `ex()`, `to_string()`,
  and `write()`/`writeln()`
