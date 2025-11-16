# Floats

Float-specific utilities for handling IEEE-754 special values like -0.0, ±INF, NaN, and hexadecimal conversion.

## Background

The IEEE-754 floating-point standard defines several special values that have unique properties:

- **-0.0 and +0.0**: Distinct values that compare as equal (`-0.0 === 0.0` returns `true`), but have different binary representations and can produce different results in certain operations (e.g., `1.0 / -0.0` returns `-INF`)
- **INF and -INF**: Positive and negative infinity, representing values too large to represent
- **NaN**: Not a Number, the result of undefined operations (e.g., `0.0 / 0.0`, `sqrt(-1)`)

This class provides utilities to work with these special values in a consistent and predictable way.

## Methods

### isNegativeZero()

```php
public static function isNegativeZero(float $value): bool
```

Determines if a floating-point number is negative zero (-0.0).

**Parameters:**
- `$value` (float) - The floating-point number to check

**Returns:**
- `bool` - Returns `true` if the value is negative zero (-0.0), `false` otherwise

**Examples:**

```php
Floats::isNegativeZero(-0.0);  // true
Floats::isNegativeZero(0.0);   // false
Floats::isNegativeZero(-1.0);  // false
```

### isPositiveZero()

```php
public static function isPositiveZero(float $value): bool
```

Determines if a floating-point number is positive zero (+0.0).

**Parameters:**
- `$value` (float) - The floating-point number to check

**Returns:**
- `bool` - Returns `true` if the value is positive zero (+0.0), `false` otherwise

**Examples:**

```php
Floats::isPositiveZero(0.0);   // true
Floats::isPositiveZero(-0.0);  // false
Floats::isPositiveZero(1.0);   // false
```

### normalizeZero()

```php
public static function normalizeZero(float $value): float
```

Normalizes negative zero to positive zero. This can be used to avoid surprising results from certain operations where the distinction between -0.0 and +0.0 matters.

**Parameters:**
- `$value` (float) - The floating-point number to normalize

**Returns:**
- `float` - Returns `0.0` if the input is `-0.0`, otherwise returns the value unchanged

**Examples:**

```php
Floats::normalizeZero(-0.0);  // 0.0
Floats::normalizeZero(0.0);   // 0.0
Floats::normalizeZero(-1.5);  // -1.5
Floats::normalizeZero(2.5);   // 2.5
```

**Use Case:** When you want consistent behavior regardless of whether a zero is positive or negative, especially in comparisons or output formatting.

### isNegative()

```php
public static function isNegative(float $value): bool
```

Check if a floating-point number is negative. This method considers -0.0 as negative (unlike the simple comparison `$value < 0`).

**Parameters:**
- `$value` (float) - The value to check

**Returns:**
- `bool` - Returns `true` for -0.0, -INF, and negative values; `false` for +0.0, INF, NaN, and positive values

**Examples:**

```php
Floats::isNegative(-1.0);   // true
Floats::isNegative(-0.0);   // true
Floats::isNegative(-INF);   // true
Floats::isNegative(0.0);    // false
Floats::isNegative(1.0);    // false
Floats::isNegative(NAN);    // false
```

**Note:** NaN is considered neither positive nor negative.

### isPositive()

```php
public static function isPositive(float $value): bool
```

Check if a floating-point number is positive. This method considers +0.0 as positive.

**Parameters:**
- `$value` (float) - The value to check

**Returns:**
- `bool` - Returns `true` for +0.0, INF, and positive values; `false` for -0.0, -INF, NaN, and negative values

**Examples:**

```php
Floats::isPositive(1.0);    // true
Floats::isPositive(0.0);    // true
Floats::isPositive(INF);    // true
Floats::isPositive(-0.0);   // false
Floats::isPositive(-1.0);   // false
Floats::isPositive(NAN);    // false
```

**Note:** NaN is considered neither positive nor negative.

### isSpecial()

```php
public static function isSpecial(float $value): bool
```

Check if a float is one of the special IEEE-754 values: NaN, -0.0, +INF, or -INF. Note that +0.0 is not considered a special value.

**Parameters:**
- `$value` (float) - The value to check

**Returns:**
- `bool` - Returns `true` if the value is NaN, -0.0, +INF, or -INF; `false` otherwise

**Examples:**

```php
Floats::isSpecial(NAN);    // true
Floats::isSpecial(-0.0);   // true
Floats::isSpecial(INF);    // true
Floats::isSpecial(-INF);   // true
Floats::isSpecial(0.0);    // false
Floats::isSpecial(1.0);    // false
Floats::isSpecial(-42.5);  // false
```

**Use Case:** Useful for validation or special handling of edge cases in numerical computations.

### toHex()

```php
public static function toHex(float $value): string
```

Convert a float to a unique 16-character hexadecimal string representation. Every possible float value produces a unique hex string, making this method ideal for hashing or keying floats in collections.

**Parameters:**
- `$value` (float) - The float to convert

**Returns:**
- `string` - A 16-character hexadecimal string representing the binary representation of the float

**Examples:**

```php
$hex1 = Floats::toHex(1.0);
$hex2 = Floats::toHex(2.0);
$hex1 !== $hex2;  // true - different values produce different hex strings

// Distinguishes between -0.0 and +0.0
Floats::toHex(-0.0) !== Floats::toHex(0.0);  // true

// Even very close values produce different hex strings
$a = 1.0;
$b = 1.0 + PHP_FLOAT_EPSILON;
Floats::toHex($a) !== Floats::toHex($b);  // true
```

**Advantages over string conversion:**
- **Uniqueness**: Unlike casting to string or using `sprintf()`, every distinct float value (including -0.0 vs +0.0) produces a unique hex string
- **Consistency**: Always produces exactly 16 characters
- **Precision**: Preserves the exact binary representation of the float
