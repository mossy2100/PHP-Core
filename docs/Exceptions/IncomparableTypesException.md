# IncomparableTypesException

Exception thrown when attempting to compare values of incompatible types.

---

## Overview

`IncomparableTypesException` is used by the `Equatable`, `Comparable`, `ApproxEquatable`, and `ApproxComparable` traits
when a comparison is attempted between values of different types that cannot be meaningfully compared. `identical()`
(provided by `Equatable`) is the one exception -- it never throws, returning `false` for different operand types.

The exception extends `InvalidArgumentException` and automatically generates a descriptive error message based on the
types of the values being compared.

---

## Class Definition

```php
namespace OceanMoon\Core\Exceptions;

class IncomparableTypesException extends InvalidArgumentException
```

---

## Constructor

```php
public function __construct(mixed $a, mixed $b)
```

Creates a new exception with an auto-generated message describing the incompatible types.

**Parameters:**

- `$a` (mixed) - The first value in the failed comparison
- `$b` (mixed) - The second value in the failed comparison

**Generated Message Format:**

```
Can't compare {typeA} with {typeB}.
```

Where `{typeA}` and `{typeB}` are the types returned by `get_debug_type()`.

---

## Usage Example

```php
use OceanMoon\Core\Exceptions\IncomparableTypesException;

class Temperature
{
    use Comparable;

    public function __construct(public float $celsius) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new IncomparableTypesException($this, $other);
        }

        return $this->celsius <=> $other->celsius;
    }
}

$temp = new Temperature(25.0);

try {
    $temp->lessThan("hot");
} catch (IncomparableTypesException $e) {
    echo $e->getMessage();
    // Output: Can't compare Temperature with string.
}
```

---

## When to Use

Throw this exception in your `equal()`, `compare()`, or `approxEqual()` implementations when:

- The `$other` value is not an instance of the expected class and cannot be converted to one.
- The types cannot be meaningfully compared.
- You want a consistent, descriptive error message for type mismatches.

---

## See Also

- **[Comparable](../Traits/Comparison/Comparable.md)** - Trait that uses this exception
- **[ApproxComparable](../Traits/Comparison/ApproxComparable.md)** - Trait that uses this exception
