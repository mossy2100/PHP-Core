# Comparable

Trait providing comparison operations for objects with natural ordering.

---

## Overview

The `Comparable` trait provides a complete set of comparison methods based on a single `compare()` method that you
implement. It uses the `Equatable` trait and adds ordering methods.

The trait follows the **Template Method Pattern** - you implement the `compare()` method, and all other methods are
automatically provided.

The trait provides the following methods:

| Name                   | Description                                         | Implementation                                             |
| ---------------------- | --------------------------------------------------- | ---------------------------------------------------------- |
| `compare()`            | Ordering comparison                                 | Todo                                                       |
| `equal()`              | Check equality                                      | Provided (delegates to `compare()`)                        |
| `identical()`          | Stricter than `equal()`, requires the same type too | Provided (via Equatable; see [Equatable.md](Equatable.md)) |
| `lessThan()`           | Check if less than                                  | Provided                                                   |
| `lessThanOrEqual()`    | Check if less than or equal to                      | Provided                                                   |
| `greaterThan()`        | Check if greater than                               | Provided                                                   |
| `greaterThanOrEqual()` | Check if greater than or equal to                   | Provided                                                   |

---

## Abstract Methods

### compare()

```php
abstract public function compare(mixed $other): int
```

**You must implement this method.** It should compare this object with another and return:

- `-1` if this object is less than `$other`
- `0` if this object equals `$other`
- `1` if this object is greater than `$other`

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `int` - Exactly `-1`, `0`, or `1`

**Implementation Guidelines:**

- Must return **exactly** -1, 0, or 1 (not just negative/zero/positive). The convenience methods use strict equality
  checks.
- Type juggling is encouraged: convert or cast `$other` to the calling object's type where a sensible conversion exists
  (e.g. via a `toX()` method), so that comparable-but-differently-typed values can be ordered.
- Should throw `IncomparableTypesException` only once no such conversion is possible or appropriate.

---

## Concrete Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this object equals another. Provided by the trait - delegates to `compare()`.

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `bool` - `true` if equal, `false` otherwise

**Behavior:**

- Returns `true` only if `compare()` returns `0`
- Propagates `IncomparableTypesException` from `compare()` for incompatible types (does not catch it) — this matches the
  `Equatable` trait's contract that `equal()` throws for incompatible types; use `identical()` if you need a version
  that never throws

**Throws:**

- `IncomparableTypesException` - If `$other` is not a compatible type

### lessThan()

```php
public function lessThan(mixed $other): bool
```

Check if this object is less than another.

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `bool` - `true` if this < other, `false` otherwise

**Throws:**

- `IncomparableTypesException` - If `$other` is not a compatible type

### lessThanOrEqual()

```php
public function lessThanOrEqual(mixed $other): bool
```

Check if this object is less than or equal to another.

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `bool` - `true` if this <= other, `false` otherwise

**Throws:**

- `IncomparableTypesException` - If `$other` is not a compatible type

### greaterThan()

```php
public function greaterThan(mixed $other): bool
```

Check if this object is greater than another.

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `bool` - `true` if this > other, `false` otherwise

**Throws:**

- `IncomparableTypesException` - If `$other` is not a compatible type

### greaterThanOrEqual()

```php
public function greaterThanOrEqual(mixed $other): bool
```

Check if this object is greater than or equal to another.

**Parameters:**

- `$other` (mixed) - The value to compare with

**Returns:**

- `bool` - `true` if this >= other, `false` otherwise

**Throws:**

- `IncomparableTypesException` - If `$other` is not a compatible type

---

## Examples

### Basic Implementation for Integers

```php
use OceanMoon\Core\Exceptions\IncomparableTypesException;
use OceanMoon\Core\Traits\Comparison\Comparable;

class Score
{
    use Comparable;

    public function __construct(
        private int $value
    ) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new IncomparableTypesException($this, $other);
        }

        if ($this->value < $other->value) {
            return -1;
        }
        if ($this->value > $other->value) {
            return 1;
        }
        return 0;
    }
}

$s1 = new Score(100);
$s2 = new Score(200);
$s3 = new Score(100);

// All comparison methods are available
var_dump($s1->lessThan($s2));           // true
var_dump($s1->equal($s3));               // true
var_dump($s2->greaterThan($s1));        // true
var_dump($s1->lessThanOrEqual($s3));    // true
```

### Using Spaceship Operator with Sign Normalization

```php
use OceanMoon\Core\Exceptions\IncomparableTypesException;
use OceanMoon\Core\Numbers;
use OceanMoon\Core\Traits\Comparison\Comparable;

class Version
{
    use Comparable;

    public function __construct(
        private int $major,
        private int $minor,
        private int $patch
    ) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new IncomparableTypesException($this, $other);
        }

        // Compare major, then minor, then patch
        $result = $this->major <=> $other->major;
        if ($result === 0) {
            $result = $this->minor <=> $other->minor;
        }
        if ($result === 0) {
            $result = $this->patch <=> $other->patch;
        }

        // Normalize to exactly -1, 0, or 1
        return Numbers::sign($result);
    }
}

$v1 = new Version(1, 2, 3);
$v2 = new Version(1, 2, 4);
$v3 = new Version(2, 0, 0);

var_dump($v1->lessThan($v2));     // true (1.2.3 < 1.2.4)
var_dump($v1->lessThan($v3));     // true (1.2.3 < 2.0.0)
var_dump($v3->greaterThan($v1));  // true (2.0.0 > 1.2.3)
```

### Comparing with Multiple Types

```php
use OceanMoon\Core\Exceptions\IncomparableTypesException;
use OceanMoon\Core\Numbers;
use OceanMoon\Core\Traits\Comparison\Comparable;

class Priority
{
    use Comparable;

    public function __construct(
        private int $value
    ) {}

    public function compare(mixed $other): int
    {
        // Handle both Priority objects and plain integers
        if ($other instanceof self) {
            $otherValue = $other->value;
        } elseif (is_int($other)) {
            $otherValue = $other;
        } else {
            throw new IncomparableTypesException($this, $other);
        }

        return Numbers::sign($this->value <=> $otherValue);
    }
}

$p = new Priority(5);

var_dump($p->greaterThan(3));              // true
var_dump($p->lessThan(new Priority(10)));  // true
var_dump($p->equal(5));                     // true
```

---

## Relationship with Other Traits

Comparable extends Equatable and adds ordering operations. It automatically provides `equal()` based on `compare()`.

For approximate comparison with ordering, use **ApproxComparable** instead.

See [ComparisonTraits.md](ComparisonTraits.md) for complete hierarchy and usage guide.

---

## Classes Using Comparable

- `OceanMoon\Quantities\Quantity` - Physical quantities with unit-aware ordering.

---

## Best Practices

1. **Return Exactly -1, 0, or 1**: Use `Numbers::sign()` or explicit conditionals to normalize the spaceship operator
   result
2. **Type Checking**: Convert or cast `$other` to the calling object's type first where a sensible conversion exists,
   and throw `IncomparableTypesException` in `compare()` only for types that remain incompatible
3. **Epsilon for Floats**: Use epsilon tolerance when comparing floating-point values via `Floats::compare()`
4. **Consistency**: Ensure `compare()` is consistent with your type's equality semantics
5. **Transitivity**: If A < B and B < C, then A < C must be true
6. **Don't Override equal()**: Unless you have a very specific reason, let the trait provide `equal()` based on
   `compare()`
7. **Use Trait Composition**: The Comparable trait already includes Equatable via trait composition - don't separately
   use Equatable

---

## See Also

- [ComparisonTraits.md](ComparisonTraits.md) - Trait hierarchy overview
- [IncomparableTypesException.md](../../Exceptions/IncomparableTypesException.md) - Exception for type mismatches

---

## Common Patterns

### Using Numbers::sign() for Normalization

```php
public function compare(mixed $other): int
{
    // ... type checking ...
    return Numbers::sign($this->value <=> $other->value);
}
```

### Multi-Field Comparison

```php
public function compare(mixed $other): int
{
    // ... type checking ...
    $result = $this->field1 <=> $other->field1;
    if ($result === 0) {
        $result = $this->field2 <=> $other->field2;
    }
    return Numbers::sign($result);
}
```
