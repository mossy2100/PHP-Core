# ApproxEquatable

Trait providing approximate equality comparison for objects with floating-point precision concerns.

---

## Overview

The `ApproxEquatable` trait extends `Equatable` by adding an `approxEqual()` method for tolerance-based comparison. This is essential for types containing floating-point values where exact equality is unreliable due to precision limitations.

The trait provides:
- `equal()` - Exact equality (from Equatable trait)
- `approxEqual()` - Approximate equality with configurable tolerances

---

## Abstract Methods

### approxEqual()

```php
abstract public function approxEqual(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
): bool
```

**You must implement this method.** It should compare this object with another using tolerance-based comparison suitable for floating-point values.

**Parameters:**
- `$other` (mixed) - The value to compare with
- `$relTol` (float) - Relative tolerance (default: 1e-9)
- `$absTol` (float) - Absolute tolerance (default: PHP_FLOAT_EPSILON ≈ 2.22e-16)

**Returns:**
- `bool` - `true` if approximately equal within tolerances, `false` otherwise

**Implementation Guidelines:**
- Should return `false` for incompatible types (not throw exceptions)
- Use combined relative and absolute tolerance: `|a - b| ≤ max(relTol * max(|a|, |b|), absTol)`
- Relative tolerance matters for large values
- Absolute tolerance matters for values near zero
- Consider using `Floats::approxEqual()` for float comparisons

---

## Examples

### Using ApproxEquatable for Complex Numbers

```php
use OceanMoon\Core\Floats;
use OceanMoon\Core\Traits\Comparison\ApproxEquatable;

class Complex
{
    use ApproxEquatable;

    public function __construct(
        private float $real,
        private float $imaginary
    ) {}

    public function equal(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->real === $other->real
            && $this->imaginary === $other->imaginary;
    }

    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        if (!$other instanceof self) {
            return false;
        }

        // Both components must be within tolerance
        return Floats::approxEqual($this->real, $other->real, $relTol, $absTol)
            && Floats::approxEqual($this->imaginary, $other->imaginary, $relTol, $absTol);
    }
}

$z1 = new Complex(3.0, 4.0);
$z2 = new Complex(3.0, 4.0);
$z3 = new Complex(3.00000001, 4.00000001);

var_dump($z1->equal($z2));        // true (exact match)
var_dump($z1->equal($z3));        // false (not exact)
var_dump($z1->approxEqual($z3));  // true (within default tolerance)
```

### Custom Tolerance for Scientific Measurements

```php
use OceanMoon\Core\Floats;
use OceanMoon\Core\Traits\Comparison\ApproxEquatable;

class Measurement
{
    use ApproxEquatable;

    private const DEFAULT_TOLERANCE = 1e-6; // 0.0001% relative tolerance

    public function __construct(
        private float $value,
        private string $unit
    ) {}

    public function equal(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->value === $other->value
            && $this->unit === $other->unit;
    }

    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        if (!$other instanceof self) {
            return false;
        }

        // Different units are never equal
        if ($this->unit !== $other->unit) {
            return false;
        }

        return Floats::approxEqual($this->value, $other->value, $relTol, $absTol);
    }
}

$m1 = new Measurement(100.0, 'kg');
$m2 = new Measurement(100.0001, 'kg');

var_dump($m1->approxEqual($m2));  // true (within custom tolerance)
```

---

## Relationship with Other Traits

ApproxEquatable extends Equatable and adds approximate equality for types with floating-point components.

Use this for types without natural ordering (e.g., Complex numbers). For types with ordering, use **ApproxComparable** instead.

See [ComparisonTraits.md](ComparisonTraits.md) for complete hierarchy and usage guide.

---

## Classes Using ApproxEquatable

- `OceanMoon\Math\Complex` - Complex numbers (no natural ordering, needs approximate equality).

---

## Best Practices

1. **Type Safety**: Always check the type of `$other` before comparing
2. **No Exceptions**: `approxEqual()` should never throw exceptions - return `false` for incompatible types
3. **Use Floats::approxEqual()**: Leverage the tested tolerance logic in `Floats::approxEqual()` for component comparisons
4. **Combined Tolerance**: Use both relative and absolute tolerance for robust comparison across different scales
5. **Default Tolerances**: Provide sensible defaults (typically `Floats::DEFAULT_RELATIVE_TOLERANCE` and `Floats::DEFAULT_ABSOLUTE_TOLERANCE`)
6. **Consistency**: Ensure approximate equality is reflexive, symmetric, and as transitive as floating-point allows
7. **Document Precision**: If your type uses custom default tolerances, document why
8. **Component-Wise**: For composite types, check each component separately with the same tolerances

---

## Tolerance Guidelines

### When to Use Relative Tolerance

Relative tolerance is important for comparing large values where absolute differences grow proportionally:

```php
$a = 1e10;
$b = 1e10 + 1;

// Absolute tolerance won't help here
Floats::approxEqual($a, $b, 0.0, 1e-10);  // false

// Relative tolerance catches this
Floats::approxEqual($a, $b, 1e-9, 0.0);   // true
```

### When to Use Absolute Tolerance

Absolute tolerance is important for comparing values near zero where relative tolerance becomes meaningless:

```php
$a = 1e-20;
$b = 2e-20;

// Relative tolerance won't help here (100% difference!)
Floats::approxEqual($a, $b, 1e-9, 0.0);   // false

// Absolute tolerance catches this
Floats::approxEqual($a, $b, 0.0, 1e-10);  // true
```

### Combined Approach

The standard formula uses both: `|a - b| ≤ max(relTol * max(|a|, |b|), absTol)`

This ensures robust comparison across all value ranges.

---

## Common Pitfalls

1. **Zero Tolerances**: Using `approxEqual($other, 0.0, 0.0)` is equivalent to exact equality - use `equal()` instead
2. **Tight Tolerances**: Very tight tolerances (< 1e-15) may not be achievable due to floating-point precision limits
3. **Unit Mismatches**: Always check units/dimensions match before comparing values
4. **Asymmetric Comparison**: Ensure `a.approxEqual(b)` implies `b.approxEqual(a)` by using symmetric tolerance logic
