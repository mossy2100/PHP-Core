# Equatable

Trait providing equality comparison functionality for objects.

---

## Overview

The `Equatable` trait provides a foundation for objects that support equality comparison. It defines an abstract
`equal()` method that must be implemented by classes using the trait.

This trait is designed to be composed with other traits in a hierarchy. It's separate from the `Comparable` trait to
follow the Interface Segregation Principle - some types can check equality but don't have a natural ordering (e.g.,
Complex numbers can be equal but don't have a meaningful less-than/greater-than relationship).

---

## Abstract Methods

### equal()

```php
abstract public function equal(mixed $other): bool
```

**You must implement this method.** Compare this object with another value and determine if they are equal.

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `bool` - `true` if the values are equal, `false` otherwise

**Why `mixed` and not `self`:**

1. `self` is invariant across both trait composition and inheritance: if a class using this trait is subclassed and the
   subclass overrides `equal()`, `self` in the override would narrow to the subclass, which PHP rejects as an
   incompatible override of the trait method (bound to the base class).
2. Some types legitimately need to compare against a related-but-different type (e.g. `Complex` accepting `int` or
   `float`). There's no type hint for "self or number", so implementations must check the type of `$other` themselves.

**Implementation Guidelines:**

- Check the type of `$other` explicitly (typically `instanceof self`) - don't attempt to convert or coerce it.
- Throw (typically `InvalidArgumentException`) for any type that isn't a deliberate, documented exception to
  same-type-only comparison. This mirrors why `==`/`!=` are avoided in favor of `===`/`!==`: silent type juggling in
  comparisons is a source of bugs. Only widen to accept a related type where there's a genuine mathematical
  justification (e.g. `Complex` and `int`/`float`), and document it.
- Should be reflexive, symmetric, and transitive.
- May use epsilon-based comparison for floating-point types (or better, use `ApproxEquatable` alongside this trait).

**Throws:**

- Typically `InvalidArgumentException` for an incompatible type - see Implementation Guidelines above.

---

## Example

```php
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
            throw new InvalidArgumentException('Cannot compare Point with ' . get_debug_type($other) . '.');
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
$p1->equal("string"); // throws InvalidArgumentException
```

---

## Relationship with Other Traits

`Equatable` is the base trait in the comparison hierarchy. Other traits extend it:

- `Comparable` adds ordering operations
- `ApproxEquatable` adds approximate equality
- `ApproxComparable` combines both

See [ComparisonTraits.md](ComparisonTraits.md) for complete hierarchy and usage guide.

---

## Classes Using Equatable

- `OceanMoon\Collections\Collection` - Base class for type-safe collections.
- `OceanMoon\Color\Color` - Encapsulates a CSS color.
- `OceanMoon\Math\Vector` - Encapsulates a vector.
- `OceanMoon\Math\Matrix` - Encapsulates a matrix (via `ApproxEquatable`).

---

## Best Practices

1. **Type Safety**: Check the type of `$other` explicitly; don't attempt silent conversion.
2. **Throw for Incompatible Types**: `equal()` should throw for types it doesn't deliberately support, matching the
   `===`/`!==` philosophy rather than `==`/`!=`.
3. **Reflexive**: `x.equal(x)` should always be `true`.
4. **Symmetric**: If `x.equal(y)` is `true`, then `y.equal(x)` must also be `true`.
5. **Transitive**: If `x.equal(y)` and `y.equal(z)` are both `true`, then `x.equal(z)` must be `true`.
6. **Consistent**: Multiple calls to `equal()` with the same arguments should return the same result.
7. **Float Comparison**: For types containing floats, use `ApproxEquatable` alongside this trait.
