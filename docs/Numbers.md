# Numbers

General number-related utility methods for working with signs and magnitudes.

## Background

This class provides utilities for working with the signs of numbers (both integers and floats), including support for IEEE-754 signed zeros (-0.0 vs +0.0). These methods are useful for mathematical operations, comparisons, and algorithms that need precise control over signs.

## Methods

### sign()

```php
public static function sign(int|float $value, bool $zero_for_zero = true): int
```

Get the sign of a number. This method supports two modes of operation depending on how you want zero values to be handled.

**Parameters:**
- `$value` (int|float) - The number to check
- `$zero_for_zero` (bool) - If `true` (default), return 0 for zero; if `false`, return the sign of zero (-1 for -0.0, 1 otherwise)

**Returns:**
- `int` - Returns 1 for positive, -1 for negative, or 0 for zero (if `$zero_for_zero` is `true`)

**Examples:**

Default behavior (return 0 for zero):
```php
Numbers::sign(42);      // 1
Numbers::sign(-42);     // -1
Numbers::sign(0);       // 0
Numbers::sign(0.0);     // 0
Numbers::sign(-0.0);    // 0
Numbers::sign(INF);     // 1
Numbers::sign(-INF);    // -1
```

With `$zero_for_zero = false` (distinguish between -0.0 and +0.0):
```php
Numbers::sign(42, false);      // 1
Numbers::sign(-42, false);     // -1
Numbers::sign(0, false);       // 1 (integer 0 is considered positive)
Numbers::sign(0.0, false);     // 1
Numbers::sign(-0.0, false);    // -1
```

**Use Cases:**
- Mathematical algorithms requiring signum function
- Comparisons where sign matters
- Working with IEEE-754 operations that distinguish -0.0 from +0.0

### copySign()

```php
public static function copySign(int|float $num, int|float $sign_source): int|float
```

Copy the sign of one number to another. Returns a value with the magnitude of the first parameter and the sign of the second parameter.

**Parameters:**
- `$num` (int|float) - The number whose magnitude to use
- `$sign_source` (int|float) - The number whose sign to copy

**Returns:**
- `int|float` - The magnitude of `$num` with the sign of `$sign_source`

**Throws:**
- `ValueError` - If NaN is passed as either parameter (NaN doesn't have a defined sign)

**Examples:**

Basic usage:
```php
Numbers::copySign(5, 10);      // 5 (positive magnitude, positive sign)
Numbers::copySign(5, -10);     // -5 (positive magnitude, negative sign)
Numbers::copySign(-5, 10);     // 5 (negative magnitude, positive sign)
Numbers::copySign(-5, -10);    // -5 (negative magnitude, negative sign)
```

With zero:
```php
Numbers::copySign(5, 0.0);     // 5 (sign of +0.0 is positive)
Numbers::copySign(5, -0.0);    // -5 (sign of -0.0 is negative)
Numbers::copySign(0.0, -10);   // -0.0 (zero with negative sign)
```

With infinity:
```php
Numbers::copySign(5, INF);     // 5
Numbers::copySign(5, -INF);    // -5
Numbers::copySign(INF, -10);   // -INF
```

Error cases:
```php
Numbers::copySign(NAN, 5);     // throws ValueError
Numbers::copySign(5, NAN);     // throws ValueError
```

**Use Cases:**
- Implementing mathematical functions that need to preserve sign relationships
- Working with algorithms that require specific sign control (e.g., coordinate transformations)
- Ensuring consistent sign handling across calculations

**Note:** Similar to C's `copysign()` function, but with explicit NaN rejection for clarity.
