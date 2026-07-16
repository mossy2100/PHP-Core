# ApproxComparable

Trait providing complete comparison operations with both exact and approximate equality for objects with natural
ordering and floating-point precision concerns.

---

## Overview

The `ApproxComparable` trait combines `Comparable` and `ApproxEquatable` to provide a complete set of comparison
operations including approximate equality. This is ideal for types with natural ordering that contain floating-point
values (e.g., Rational numbers).

The trait provides the following methods:

| Name                   | Description                                    | Implementation              |
| ---------------------- | ---------------------------------------------- | --------------------------- |
| `compare()`            | Exact ordering comparison                      | Todo                        |
| `approxEqual()`        | Approximate equality                           | Todo                        |
| `approxCompare()`      | Approximate ordering comparison with tolerance | Provided                    |
| `equal()`              | Exact equality                                 | Provided (via `Comparable`) |
| `lessThan()`           | Check if less than                             | Provided (via `Comparable`) |
| `lessThanOrEqual()`    | Check if less than or equal to                 | Provided (via `Comparable`) |
| `greaterThan()`        | Check if greater than                          | Provided (via `Comparable`) |
| `greaterThanOrEqual()` | Check if greater than or equal to              | Provided (via `Comparable`) |

---

## Abstract Methods

### compare()

```php
abstract public function compare(mixed $other): int
```

**You must implement this method.** See [Comparable.md](Comparable.md) for full documentation.

### approxEqual()

```php
abstract public function approxEqual(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
): bool
```

**You must implement this method.** See [ApproxEquatable.md](ApproxEquatable.md) for full documentation.

Both abstract methods should check the type of `$other` explicitly (typically `instanceof self`) and throw (typically
`InvalidArgumentException`) for anything that isn't a deliberate, documented exception to same-type-only comparison -
see [Equatable.md](Equatable.md) for the reasoning.

---

## Concrete Methods

### approxCompare()

```php
public function approxCompare(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
): int
```

Compare with approximate equality awareness. Returns 0 if values are approximately equal within tolerances, otherwise
performs exact comparison. Provided by the trait, built on `approxEqual()` and `compare()`.

**Use Cases:**

- Sorting with approximate equality "buckets"
- Finding approximate min/max
- Range queries with tolerance

---

## Example

```php
use OceanMoon\Core\Floats;
use OceanMoon\Core\Numbers;
use OceanMoon\Core\Traits\Comparison\ApproxComparable;

class Rational
{
    use ApproxComparable;

    public function __construct(private int $num, private int $den) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new InvalidArgumentException('Cannot compare Rational with ' . get_debug_type($other) . '.');
        }

        $left = $this->num * $other->den;
        $right = $other->num * $this->den;

        return Numbers::sign($left <=> $right);
    }

    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        if (!$other instanceof self) {
            throw new InvalidArgumentException('Cannot compare Rational with ' . get_debug_type($other) . '.');
        }

        return Floats::approxEqual(
            $this->num / $this->den,
            $other->num / $other->den,
            $relTol,
            $absTol
        );
    }

    // equal(), lessThan(), greaterThan(), approxCompare(), etc. all provided
}
```

---

## Relationship with Other Traits

`ApproxComparable` combines `Comparable` and `ApproxEquatable`, providing the complete comparison suite for ordered
types with floating-point components.

See [ComparisonTraits.md](ComparisonTraits.md) for complete hierarchy and usage guide.

---

## Classes Using ApproxComparable

- `OceanMoon\Math\Rational` - Rational numbers, require approximate equality and less/greater than comparisons.
- `OceanMoon\Quantities\Quantity` - Physical quantities with unit-aware ordering.

---

## Best Practices

1. **Implement Both**: Provide both exact (`compare()`) and approximate (`approxEqual()`) implementations.
2. **Consistent Semantics**: Ensure approximate equality aligns with your ordering semantics.
3. **Don't Override approxCompare()**: Let the trait provide it based on `approxEqual()` and `compare()`.
4. **Type Safety**: Check the type of `$other` explicitly in both `compare()` and `approxEqual()` - throw for anything
   that isn't a deliberate, documented exception to same-type-only comparison.
5. **Use Floats Utilities**: Leverage `Floats::approxEqual()` and `Floats::compare()` for float comparisons.
6. **Sensible Defaults**: Choose default tolerances appropriate for your type's typical use cases.

---

## When to Use Each Method

### Use `equal()` when:

- You need exact equality
- Comparing integer-only types
- Working with canonical forms (e.g., reduced fractions)

### Use `approxEqual()` when:

- Comparing floating-point results
- Dealing with accumulated rounding errors
- Checking if values are "close enough" for practical purposes

### Use `compare()` when:

- Sorting with strict ordering
- Finding exact min/max
- Binary search with exact matching

### Use `approxCompare()` when:

- Sorting with tolerance "buckets"
- Finding approximate min/max
- Range queries with tolerance

---

## See Also

- [ComparisonTraits.md](ComparisonTraits.md) - Trait hierarchy overview
- [Comparable.md](Comparable.md) - Base ordering trait
- [ApproxEquatable.md](ApproxEquatable.md) - Approximate equality trait
