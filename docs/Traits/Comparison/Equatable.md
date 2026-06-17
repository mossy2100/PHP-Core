# Equatable

Trait providing equality comparison functionality for objects.

---

## Overview

The `Equatable` trait provides a foundation for objects that support equality comparison. It defines an abstract `equal()` method that must be implemented by classes using the trait.

This trait is designed to be composed with other traits in a hierarchy. It's separate from the `Comparable` trait to follow the Interface Segregation Principle - some types can check equality but don't have a natural ordering (e.g., Complex numbers can be equal but don't have a meaningful less-than/greater-than relationship).

The trait provides:
- `equal()` - Abstract method for exact equality comparison

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
- Should return `false` for incompatible types (not throw exceptions)
- Should handle type checking gracefully
- May use epsilon-based comparison for floating-point types
- Should be consistent with the object's equality semantics

---

## Examples

### Using Equatable for Value Objects

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
            return false;
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
var_dump($p1->equal("string")); // false (gracefully handles wrong type)
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
2. **No Exceptions**: `equal()` should never throw exceptions - return `false` for incompatible types
3. **Reflexive**: `x.equal(x)` should always be `true`
4. **Symmetric**: If `x.equal(y)` is `true`, then `y.equal(x)` must also be `true`
5. **Transitive**: If `x.equal(y)` and `y.equal(z)` are both `true`, then `x.equal(z)` must be `true`
6. **Consistent**: Multiple calls to `equal()` with the same arguments should return the same result
7. **Null/Type Handling**: `x.equal($other)` where `$other` is a different type should return `false`
8. **Float Comparison**: For types containing floats, consider epsilon-based comparison to handle precision issues
