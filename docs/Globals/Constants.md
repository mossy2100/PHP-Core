# Constants

Shared constants used by Core, Math, and other packages.

---

## Overview

`src/Globals/constants.php` provides a small set of namespaced constants (`OceanMoon\Core`) that don't belong to any
single class.

---

## Autoloading

Since these are namespaced constants rather than class constants, PSR-4 autoloading won't discover them automatically.
The Core package's `composer.json` includes a `files` autoload entry covering all of `src/Globals/`. To use a constant
without qualifying the namespace every time, add a `use const` import:

```php
use const OceanMoon\Core\M_TAU;
```

See [Strings.md](Strings.md#autoloading) for the full `files` autoload configuration.

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

## See Also

- **[Strings](Strings.md)** - String conversion and printing functions
- **[Numbers](Numbers.md)** - Number-related functions
- **[Arrays](../Arrays.md)** - `removeRecursion()`, which uses the `RECURSION` marker
- **[Stringify](../Stringify.md)** - Also uses the `RECURSION` marker when stringifying circular structures
