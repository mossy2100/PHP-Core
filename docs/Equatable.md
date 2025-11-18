# Equatable

Interface for objects that can be compared for equality.

## Overview

The `Equatable` interface defines a contract for objects that support equality comparison. It provides a single method, `equals()`, that compares this object with another and returns whether they are equal.

This interface is separate from the `Comparable` trait to follow the Interface Segregation Principle - some types can check equality but don't have a natural ordering (e.g., Complex numbers can be equal but don't have a meaningful less-than/greater-than relationship).

## Interface Definition

```php
interface Equatable
{
    /**
     * Compare two objects and return true if they are equal.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the two values are equal, false otherwise.
     */
    public function equals(mixed $other): bool;
}
```

## Method

### equals()

```php
public function equals(mixed $other): bool
```

Compare this object with another value and determine if they are equal.

**Parameters:**
- `$other` (mixed) - The value to compare with (can be any type)

**Returns:**
- `bool` - `true` if the values are equal, `false` otherwise

**Implementation Guidelines:**
- Should return `false` for incompatible types (not throw exceptions)
- Should handle type checking gracefully
- May use epsilon-based comparison for floating-point types
- Should be consistent with the object's equality semantics

## Examples

### Implementing Equatable for Value Objects

```php
use Galaxon\Core\Equatable;

class Point implements Equatable
{
    public function __construct(
        private float $x,
        private float $y
    ) {}

    public function equals(mixed $other): bool
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

var_dump($p1->equals($p2)); // true
var_dump($p1->equals($p3)); // false
var_dump($p1->equals("string")); // false (gracefully handles wrong type)
```

### Epsilon-Based Equality for Floating-Point Types

```php
use Galaxon\Core\Equatable;

class Temperature implements Equatable
{
    private const EPSILON = 0.01; // 0.01° tolerance

    public function __construct(
        private float $celsius
    ) {}

    public function equals(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return abs($this->celsius - $other->celsius) < self::EPSILON;
    }
}

$t1 = new Temperature(20.00);
$t2 = new Temperature(20.005); // Within tolerance
$t3 = new Temperature(20.02);  // Outside tolerance

var_dump($t1->equals($t2)); // true (within epsilon)
var_dump($t1->equals($t3)); // false (outside epsilon)
```

### Complex Equality with Custom Logic

```php
use Galaxon\Core\Equatable;

class Money implements Equatable
{
    public function __construct(
        private float $amount,
        private string $currency
    ) {}

    public function equals(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        // Must have same currency AND same amount
        return $this->currency === $other->currency
            && abs($this->amount - $other->amount) < 0.01;
    }
}

$usd1 = new Money(100.00, 'USD');
$usd2 = new Money(100.00, 'USD');
$eur = new Money(100.00, 'EUR');

var_dump($usd1->equals($usd2)); // true (same currency and amount)
var_dump($usd1->equals($eur));  // false (different currency)
```

## Relationship with Comparable

For types that have a natural ordering, use the `Comparable` trait instead. The `Comparable` trait:
- Provides comparison methods (`isLessThan()`, `isGreaterThan()`, etc.)
- Includes an `equals()` implementation based on `compare()`
- Requires implementing an abstract `compare()` method

See [Comparable.md](Comparable.md) for details.

## Core Classes Implementing Equatable

- `Angle` - Angular measurements (uses epsilon-based comparison)
- `Complex` - Complex numbers (uses epsilon-based comparison, customizable)
- `Rational` - Rational numbers (uses exact comparison)
- `Collection`, `Dictionary`, `Set`, `Sequence` - Collection types (structural equality)
- `Color` - Color values

## Best Practices

1. **Type Safety**: Always check the type of `$other` before comparing
2. **No Exceptions**: `equals()` should never throw exceptions - return `false` for incompatible types
3. **Reflexive**: `x.equals(x)` should always be `true`
4. **Symmetric**: If `x.equals(y)` is `true`, then `y.equals(x)` must also be `true`
5. **Transitive**: If `x.equals(y)` and `y.equals(z)` are both `true`, then `x.equals(z)` must be `true`
6. **Consistent**: Multiple calls to `equals()` with the same arguments should return the same result
7. **Null/Type Handling**: `x.equals($other)` where `$other` is a different type should return `false`
8. **Float Comparison**: For types containing floats, consider epsilon-based comparison to handle precision issues
