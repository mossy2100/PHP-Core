# Numbers

Convenient functions for working with numbers.

---

## Overview

`src/Globals/numbers.php` provides functions — namespaced under `OceanMoon\Core\Globals` — for type-checking,
zero-checking, and sign-related operations on `int`/`float` values, including correct handling of IEEE-754 signed zeros
(`-0.0` vs `+0.0`).

---

## Autoloading

Since these are functions rather than classes, PSR-4 autoloading won't discover them automatically. The Core package's
`composer.json` includes a `files` autoload entry covering all of `src/Globals/`. To use these functions without
qualifying the namespace every time, add a `use function` import:

```php
use function OceanMoon\Core\Globals\is_number;
use function OceanMoon\Core\Globals\is_zero;
use function OceanMoon\Core\Globals\sign;
use function OceanMoon\Core\Globals\copy_sign;
```

See [Strings.md](Strings.md#autoloading) for the full `files` autoload configuration.

---

## Inspection Functions

### is_number()

```php
function is_number(mixed $value): bool
```

Check if a value is a number, i.e. an `int` or a `float`. This differs from PHP's built-in `is_numeric()`, which also
returns `true` for numeric strings like `"42"` or `"3.14"`.

**Parameters:**

- `$value` (mixed) - The value to check.

**Returns:**

- `bool` - `true` if the value is an `int` or `float`, `false` otherwise.

**Examples:**

```php
is_number(42);        // true
is_number(3.14);      // true
is_number(INF);       // true
is_number(NAN);       // true
is_number('42');      // false (numeric string)
is_number('hello');   // false
is_number(true);      // false
is_number(null);      // false
```

**Comparison with `is_numeric()`:**

| Value    | `is_number()` | `is_numeric()` |
| -------- | ------------- | -------------- |
| `42`     | `true`        | `true`         |
| `3.14`   | `true`        | `true`         |
| `'42'`   | `false`       | `true`         |
| `'3.14'` | `false`       | `true`         |
| `'0x1A'` | `false`       | `true`         |
| `true`   | `false`       | `false`        |
| `null`   | `false`       | `false`        |

### is_zero()

```php
function is_zero(int|float $value): bool
```

Check if a number is zero. Returns `true` for integer `0` and float `±0.0`.

**Parameters:**

- `$value` (int|float) - The number to check.

**Returns:**

- `bool` - `true` if the value is zero, `false` otherwise.

**Examples:**

```php
is_zero(0);       // true
is_zero(0.0);     // true
is_zero(-0.0);    // true
is_zero(1);       // false
is_zero(0.1);     // false
is_zero(INF);     // false
is_zero(NAN);     // false
```

## Sign Functions

### sign()

```php
function sign(int|float $value, bool $zeroForZero = true): int
```

Get the sign of a number. Has two modes of operation, controlled by `$zeroForZero`.

**Parameters:**

- `$value` (int|float) - The number to check.
- `$zeroForZero` (bool) - If `true` (default), return `0` for a zero value. If `false`, return the sign of the zero
  instead: `-1` for the special float value `-0.0`, or `1` for integer `0` or float `+0.0`.

**Returns:**

- `int` - Exactly `-1`, `0`, or `1`.

**Examples:**

Default behavior (`0` for zero):

```php
sign(42);      // 1
sign(-42);     // -1
sign(0);       // 0
sign(0.0);     // 0
sign(-0.0);    // 0
sign(INF);     // 1
sign(-INF);    // -1
```

With `$zeroForZero = false` (distinguish `-0.0` from `+0.0`):

```php
sign(42, false);      // 1
sign(-42, false);     // -1
sign(0, false);       // 1 (integer 0 is considered positive)
sign(0.0, false);     // 1
sign(-0.0, false);    // -1
```

### copy_sign()

```php
function copy_sign(int|float $num, int|float $signSource): int|float
```

Copy the sign of one number to another. Returns a value with the magnitude of `$num` and the sign of `$signSource`.
Similar to C's `copysign()` function.

**Parameters:**

- `$num` (int|float) - The number whose magnitude to use.
- `$signSource` (int|float) - The number whose sign to copy.

**Returns:**

- `int|float` - The magnitude of `$num` with the sign of `$signSource`.

**Throws:**

- `DomainException` - If `NAN` is passed as either parameter (`NAN` has no defined sign).

**Examples:**

Basic usage:

```php
copy_sign(5, 10);      // 5 (positive magnitude, positive sign)
copy_sign(5, -10);     // -5 (positive magnitude, negative sign)
copy_sign(-5, 10);     // 5 (negative magnitude, positive sign)
copy_sign(-5, -10);    // -5 (negative magnitude, negative sign)
```

With zero:

```php
copy_sign(5, 0.0);     // 5 (sign of +0.0 is positive)
copy_sign(5, -0.0);    // -5 (sign of -0.0 is negative)
copy_sign(0.0, -10);   // -0.0 (zero with negative sign)
```

With infinity:

```php
copy_sign(5, INF);     // 5
copy_sign(5, -INF);    // -5
copy_sign(INF, -10);   // -INF
```

Error cases:

```php
copy_sign(NAN, 5);     // throws DomainException
copy_sign(5, NAN);     // throws DomainException
```

---

## See Also

- **[Floats](../Floats.md)** - Float-specific utility methods, including `approxEqual()` for approximate comparisons
- **[Integers](../Integers.md)** - Integer-specific utility methods
- **[Types](../Types.md)** - General type checking and inspection utilities
- **[Constants](Constants.md)** - Shared constants, including `NUMBER_REGEX`
