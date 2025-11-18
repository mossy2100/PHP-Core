# Comparable

Trait providing comparison operations for objects with natural ordering.

## Overview

The `Comparable` trait provides a complete set of comparison methods based on a single `compare()` method that you implement. It includes:
- `equals()` - Check equality (satisfies the `Equatable` interface)
- `isLessThan()` - Check if less than
- `isLessThanOrEqual()` - Check if less than or equal to
- `isGreaterThan()` - Check if greater than
- `isGreaterThanOrEqual()` - Check if greater than or equal to

The trait follows the **Template Method Pattern** - you implement the `compare()` method, and all other methods are automatically provided.

## Trait Definition

```php
trait Comparable
{
    /**
     * Compare two objects and return an integer indicating ordering.
     *
     * @param mixed $other The value to compare with.
     * @return int Must return exactly -1, 0, or 1.
     */
    abstract public function compare(mixed $other): int;

    public function equals(mixed $other): bool;
    public function isLessThan(mixed $other): bool;
    public function isLessThanOrEqual(mixed $other): bool;
    public function isGreaterThan(mixed $other): bool;
    public function isGreaterThanOrEqual(mixed $other): bool;
}
```

## Methods

### compare()

```php
abstract public function compare(mixed $other): int
```

**You must implement this method.** It should compare this object with another and return:
- `-1` if this object is less than `$other`
- `0` if this object equals `$other`
- `1` if this object is greater than `$other`

**Important:** Must return **exactly** -1, 0, or 1 (not just negative/zero/positive). The convenience methods use strict equality checks.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `int` - Exactly `-1`, `0`, or `1`

**Implementation Guidelines:**
- May throw `TypeError` for incompatible types (this is expected behavior)
- Should use epsilon tolerance for floating-point comparisons
- Must be consistent with your type's equality semantics

### equals()

```php
public function equals(mixed $other): bool
```

Check if this object equals another. Provided by the trait - delegates to `compare()`.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - `true` if equal, `false` otherwise

**Implementation:**
```php
return $other instanceof static && $this->compare($other) === 0;
```

Note: Returns `false` gracefully for incompatible types (doesn't throw).

### isLessThan()

```php
public function isLessThan(mixed $other): bool
```

Check if this object is less than another.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - `true` if this < other, `false` otherwise

**Implementation:**
```php
return $this->compare($other) === -1;
```

### isLessThanOrEqual()

```php
public function isLessThanOrEqual(mixed $other): bool
```

Check if this object is less than or equal to another.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - `true` if this <= other, `false` otherwise

**Implementation:**
```php
return !$this->isGreaterThan($other);
```

### isGreaterThan()

```php
public function isGreaterThan(mixed $other): bool
```

Check if this object is greater than another.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - `true` if this > other, `false` otherwise

**Implementation:**
```php
return $this->compare($other) === 1;
```

### isGreaterThanOrEqual()

```php
public function isGreaterThanOrEqual(mixed $other): bool
```

Check if this object is greater than or equal to another.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - `true` if this >= other, `false` otherwise

**Implementation:**
```php
return !$this->isLessThan($other);
```

## Examples

### Basic Implementation for Integers

```php
use Galaxon\Core\Comparable;
use Galaxon\Core\Equatable;

class Score implements Equatable
{
    use Comparable;

    public function __construct(
        private int $value
    ) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new TypeError('Can only compare with another Score');
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
var_dump($s1->isLessThan($s2));           // true
var_dump($s1->equals($s3));               // true
var_dump($s2->isGreaterThan($s1));        // true
var_dump($s1->isLessThanOrEqual($s3));    // true
```

### Using Spaceship Operator with Sign Normalization

```php
use Galaxon\Core\Comparable;
use Galaxon\Core\Equatable;
use Galaxon\Core\Numbers;

class Version implements Equatable
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
            throw new TypeError('Can only compare with another Version');
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

var_dump($v1->isLessThan($v2));     // true (1.2.3 < 1.2.4)
var_dump($v1->isLessThan($v3));     // true (1.2.3 < 2.0.0)
var_dump($v3->isGreaterThan($v1));  // true (2.0.0 > 1.2.3)
```

### With Epsilon-Based Comparison for Floats

```php
use Galaxon\Core\Comparable;
use Galaxon\Core\Equatable;

class Distance implements Equatable
{
    use Comparable;

    private const EPSILON = 1e-9;

    public function __construct(
        private float $meters
    ) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new TypeError('Can only compare with another Distance');
        }

        // Use epsilon tolerance for equality
        $diff = $this->meters - $other->meters;
        if (abs($diff) < self::EPSILON) {
            return 0;
        }

        return $diff < 0 ? -1 : 1;
    }
}

$d1 = new Distance(10.0);
$d2 = new Distance(10.0 + 1e-10); // Within epsilon
$d3 = new Distance(20.0);

var_dump($d1->equals($d2));         // true (within epsilon)
var_dump($d1->isLessThan($d3));     // true
```

### Comparing with Multiple Types

```php
use Galaxon\Core\Comparable;
use Galaxon\Core\Equatable;
use Galaxon\Core\Numbers;

class Priority implements Equatable
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
            throw new TypeError('Can only compare with Priority or int');
        }

        return Numbers::sign($this->value <=> $otherValue);
    }
}

$p = new Priority(5);

var_dump($p->isGreaterThan(3));              // true
var_dump($p->isLessThan(new Priority(10)));  // true
var_dump($p->equals(5));                     // true
```

## Relationship with Equatable

Classes using `Comparable` should typically also implement the `Equatable` interface:

```php
class MyClass implements Equatable
{
    use Comparable;

    public function compare(mixed $other): int
    {
        // Implementation
    }
}
```

The trait provides `equals()` automatically, satisfying the `Equatable` interface contract.

## Core Classes Using Comparable

- `Angle` - Angular measurements with epsilon-based comparison
- `Rational` - Rational numbers with exact comparison

## Best Practices

1. **Return Exactly -1, 0, or 1**: Use `Numbers::sign()` or explicit conditionals to normalize the spaceship operator result
2. **Type Checking**: Throw `TypeError` in `compare()` for incompatible types (don't try to handle them)
3. **Epsilon for Floats**: Use epsilon tolerance when comparing floating-point values
4. **Consistency**: Ensure `compare()` is consistent with your type's equality semantics
5. **Transitivity**: If A < B and B < C, then A < C must be true
6. **Implement Equatable**: Classes using this trait should implement the `Equatable` interface
7. **Don't Override equals()**: Unless you have a very specific reason, let the trait provide `equals()` based on `compare()`

## Common Patterns

### Using Numbers::sign() for Normalization

```php
public function compare(mixed $other): int
{
    // ... type checking ...
    return Numbers::sign($this->value <=> $other->value);
}
```

### Epsilon-Based Float Comparison

```php
public function compare(mixed $other): int
{
    // ... type checking ...
    $diff = $this->floatValue - $other->floatValue;
    if (abs($diff) < self::EPSILON) {
        return 0;
    }
    return $diff < 0 ? -1 : 1;
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
