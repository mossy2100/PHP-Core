# Equatable

Trait providing equality comparison functionality for objects.

---

## Overview

The `Equatable` trait provides a foundation for objects that support equality comparison. It defines an abstract
`equal()` method that must be implemented by classes using the trait.

This trait is designed to be composed with other traits in a hierarchy. It's separate from the `Comparable` trait to
follow the Interface Segregation Principle - some types can check equality but don't have a natural ordering (e.g.,
Complex numbers can be equal but don't have a meaningful less-than/greater-than relationship).

The trait provides the following methods:

| Name          | Description                                         | Implementation                |
| ------------- | --------------------------------------------------- | ----------------------------- |
| `equal()`     | Exact equality comparison                           | Todo                          |
| `identical()` | Stricter than `equal()`, requires the same type too | Provided (built on `equal()`) |

---

## Abstract Methods

### equal()

```php
abstract public function equal(mixed $other): bool
```

**You must implement this method.** Compare this object with another value and determine if they are equal.

**Parameters:**

- `$other` (mixed) - The value to compare with (can be any type)

**Returns:**

- `bool` - `true` if the values are equal, `false` otherwise

**Implementation Guidelines:**

- Should attempt to convert or cast `$other` to the calling object's type where a sensible conversion exists (e.g. via a
  `toX()` method), so that comparable-but-differently-typed values (e.g. `int` vs `Complex`) can still be compared
- Should throw `IncomparableTypesException` only once no such conversion is possible or appropriate
- May use epsilon-based comparison for floating-point types
- Should be consistent with the object's equality semantics

**Throws:**

- `IncomparableTypesException` - If `$other` is not a compatible type

---

## Provided Methods

### identical()

```php
public function identical(mixed $other): bool
```

A stricter counterpart to `equal()`, provided automatically — no implementation needed. Returns `true` only if `$other`
is the exact same type (via `Types::same()`, so a subclass of a non-final class doesn't count) _and_ `equal()` to it.

This only behaves differently from `equal()` for classes that deliberately widen `equal()` to accept other types (e.g.
accepting a plain `int` alongside instances of the same class). For a class whose `equal()` is already strict (only ever
accepts instances of the same class), `identical()` will always agree with `equal()` — which is correct, just not
particularly interesting.

**Parameters:**

- `$other` (mixed) - The value to compare with (can be any type)

**Returns:**

- `bool` - `true` if `$other` is the same type and `equal()` to this object, `false` otherwise

**Example:**

```php
class Money
{
    use Equatable;

    public function __construct(private readonly int $cents) {}

    public function equal(mixed $other): bool
    {
        // Also accepts a plain int, treated as a whole-dollar amount.
        if (is_int($other)) {
            return $this->cents === $other * 100;
        }
        return $other instanceof self && $this->cents === $other->cents;
    }
}

$m1 = new Money(500);
$m2 = new Money(500);

var_dump($m1->equal($m2));      // true
var_dump($m1->equal(5));        // true -- widened equal()
var_dump($m1->identical($m2));  // true -- same type, equal
var_dump($m1->identical(5));    // false -- not the same type, even though equal(5) is true
```

---

## Examples

### Using Equatable for Value Objects

```php
use OceanMoon\Core\Exceptions\IncomparableTypesException;
use OceanMoon\Core\Traits\Comparison\Equatable;

class Point
{
    use Equatable;

    public function __construct(
        private float $x,
        private float $y
    ) {}

    public function equal(mixed $other): bool
    {
        if (!$other instanceof self) {
            throw new IncomparableTypesException($this, $other);
        }

        return $this->x === $other->x
            && $this->y === $other->y;
    }
}

$p1 = new Point(3.0, 4.0);
$p2 = new Point(3.0, 4.0);
$p3 = new Point(5.0, 6.0);

var_dump($p1->equal($p2)); // true
var_dump($p1->equal($p3)); // false
$p1->equal("string"); // throws IncomparableTypesException
```

---

## Relationship with Other Traits

Equatable is the base trait in the comparison hierarchy. Other traits extend it:

- **Comparable** adds ordering operations
- **ApproxEquatable** adds approximate equality
- **ApproxComparable** combines both

See [ComparisonTraits.md](ComparisonTraits.md) for complete hierarchy and usage guide.

---

## Classes Using Equatable

- `OceanMoon\Collections\Collection` - Base class for type-safe collections.
- `OceanMoon\Color\Color` - Encapsulates a CSS color.

---

## Best Practices

1. **Type Safety**: Always check the type of `$other` before comparing
2. **Throw for Incompatible Types**: `equal()` should throw `IncomparableTypesException` for types it can't meaningfully
   compare against. Use `identical()` (provided by this trait) when you need a version that never throws.
3. **Reflexive**: `x.equal(x)` should always be `true`
4. **Symmetric**: If `x.equal(y)` is `true`, then `y.equal(x)` must also be `true`
5. **Transitive**: If `x.equal(y)` and `y.equal(z)` are both `true`, then `x.equal(z)` must be `true`
6. **Consistent**: Multiple calls to `equal()` with the same arguments should return the same result
7. **Float Comparison**: For types containing floats, consider epsilon-based comparison to handle precision issues
