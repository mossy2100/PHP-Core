# Floats

A comprehensive utility class for working with floating-point numbers in PHP, providing tools for comparison, conversion, navigation, random generation, and handling of IEEE-754 special values.

## Background

Floating-point arithmetic presents several challenges that this class helps address:

### Comparison Issues

Direct comparison of floats using `===` often fails due to precision loss in calculations:

```php
0.1 + 0.2 === 0.3;  // false (!)
```

The `approxEqual()` method provides reliable approximate comparison with configurable tolerance.

### IEEE-754 Special Values

The IEEE-754 standard defines several special values with unique properties:

- **-0.0 and +0.0**: Distinct values that compare as equal (`-0.0 === 0.0` returns `true`), but have different binary representations and can produce different results in certain operations (e.g., `1.0 / -0.0` returns `-INF`)
- **INF and -INF**: Positive and negative infinity, representing values too large to represent
- **NaN**: Not a Number, the result of undefined operations (e.g., `0.0 / 0.0`, `sqrt(-1)`)

Several methods are provided to facilitate working with these values: `isNegativeZero()`, `isPositiveZero()`, `isSpecial()`, and `normalizeZero()`.

### Float-to-Integer Conversion

Converting floats to integers can lose precision. The `tryConvertToInt()` method provides safe, lossless conversion when possible.

### Navigating the Float Space

The `next()` and `previous()` methods allow traversal of the IEEE-754 number line, useful for testing edge cases and understanding float precision.

### Random Float Generation

Two methods provide random floats for different use cases: `rand()` explores the full float space for fuzzing, while `randInRange()` generates values within specific bounds.

## Methods

### approxEqual()

```php
public static function approxEqual(float $f1, float $f2, float $epsilon = 1e-10): bool
```

Check if two floats are approximately equal within a given epsilon (tolerance). This is the recommended way to compare floating-point numbers for equality, as direct comparison (`===`) can fail due to precision issues.

**Parameters:**
- `$f1` (float) - The first float
- `$f2` (float) - The second float
- `$epsilon` (float) - The maximum allowed absolute difference between the two floats (default: `1e-10`)

**Returns:**
- `bool` - Returns `true` if the absolute difference between the two floats is less than or equal to epsilon, `false` otherwise

**Throws:**
- `ValueError` - If epsilon is negative

**Examples:**

```php
// Direct float comparison can fail due to precision issues
0.1 + 0.2 === 0.3;  // false (!)

// Use approxEqual instead
Floats::approxEqual(0.1 + 0.2, 0.3);  // true

// Identical values
Floats::approxEqual(1.0, 1.0);  // true

// Values within default epsilon (1e-10)
Floats::approxEqual(1.0, 1.0 + 1e-11);  // true
Floats::approxEqual(1.0, 1.0 + 1e-9);   // false

// Custom epsilon for looser comparison
Floats::approxEqual(1.0, 1.1, 0.2);  // true
Floats::approxEqual(1.0, 1.3, 0.2);  // false

// Zero epsilon for exact comparison
Floats::approxEqual(1.0, 1.0, 0.0);  // true
Floats::approxEqual(1.0, 1.0 + PHP_FLOAT_EPSILON, 0.0);  // false

// Handles positive and negative zero
Floats::approxEqual(0.0, -0.0);  // true
```

**Behavior:**
- Uses **absolute difference**: `abs($f1 - $f2) <= $epsilon`
- **Symmetric**: `approxEqual($a, $b)` equals `approxEqual($b, $a)`
- Positive and negative zero are considered equal (their difference is 0)

**Choosing Epsilon:**
- `1e-10` (default): Good for most general-purpose comparisons
- `1e-6` to `1e-9`: Suitable for results of moderate computation
- `1e-14` to `1e-15`: Near machine precision for doubles
- Domain-specific: Use tolerances appropriate to your application (e.g., 0.01 for percentages)

**Use Cases:**
- Comparing results of floating-point calculations
- Unit testing numerical code
- Checking convergence in iterative algorithms
- Color space conversions (RGB ↔ HSL)

**Note:** This method uses absolute difference, which works well when values are near zero or of similar magnitude. For comparing values of very different magnitudes, consider relative comparison methods.

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

### tryConvertToInt()

```php
public static function tryConvertToInt(float $f, ?int &$i): bool
```

Attempt to convert a float to an integer losslessly. Returns `true` if the conversion succeeds (the float represents a whole number within the integer range), `false` otherwise.

**Parameters:**
- `$f` (float) - The float to convert
- `$i` (int|null) - Output parameter that receives the converted integer on success

**Returns:**
- `bool` - `true` if the float can be converted to an integer losslessly, `false` otherwise

**Behavior:**
- Returns `true` and sets `$i` if the float equals a whole number (e.g., 5.0, -10.0, 0.0)
- Returns `false` and leaves `$i` unchanged if the float has a fractional part (e.g., 5.5, 0.1)
- Handles negative zero (-0.0) by converting it to integer 0
- Works for any float value (without fractional part) within PHP's integer range (PHP_INT_MIN to PHP_INT_MAX)
    
**Examples:**

```php
// Successful conversion - whole number
$f = 5.0;
if (Floats::tryConvertToInt($f, $i)) {
    echo $i; // 5
}

// Failed conversion - fractional part
$f = 5.5;
$i = null;
if (!Floats::tryConvertToInt($f, $i)) {
    echo "Cannot convert"; // $i remains null
}

// Large whole numbers
$f = 1000000.0;
Floats::tryConvertToInt($f, $i); // true, $i = 1000000

// Negative zero
$f = -0.0;
Floats::tryConvertToInt($f, $i); // true, $i = 0

// Powers of 2 work well (within precision)
$f = (float)(1 << 50); // 2^50
Floats::tryConvertToInt($f, $i); // true

// PHP_INT_MIN is -2^63 (a power of 2), so it converts exactly
$f = (float)PHP_INT_MIN;
Floats::tryConvertToInt($f, $i); // true, $i = PHP_INT_MIN

// PHP_INT_MAX is 2^63-1 (not a power of 2), loses precision as float
$f = (float)PHP_INT_MAX;
Floats::tryConvertToInt($f, $i); // false (loses precision)
```

**Use Cases:**
- Optimizing constructors that accept `int|float` by avoiding expensive float-to-rational conversions when possible
- Validating that a float represents a whole number before casting
- Conditional type conversion in generic numeric code

**Precision Limits:**
On 64-bit systems, floats can exactly represent integers up to 2^53 (9,007,199,254,740,992). Beyond this, not all integers can be represented exactly as floats. Powers of 2 can be represented exactly up to much larger values.

### next()

```php
public static function next(float $f): float
```

Returns the next representable floating-point number after the given value. This performs bit-level manipulation to move to the adjacent float in the IEEE-754 number line.

**Parameters:**
- `$f` (float) - The given number

**Returns:**
- `float` - The next floating-point number after the given number

**Behavior:**
- For positive numbers: returns the next larger float
- For negative numbers: returns a float closer to zero
- `-0.0` → `+0.0`
- `PHP_FLOAT_MAX` → `INF`
- `INF` → `INF`
- `-INF` → `-PHP_FLOAT_MAX`
- `NAN` → `NAN`

**Examples:**

```php
$f = 1.0;
$next = Floats::next($f);
// $next > $f (next representable float after 1.0)

// Navigate from negative zero to smallest positive number
$f = -0.0;
$next = Floats::next($f);  // 0.0
$next2 = Floats::next($next);  // smallest positive float

// At the boundary
$next = Floats::next(PHP_FLOAT_MAX);  // INF
```

**Use Cases:**
- Implementing "nextafter" functionality for numerical algorithms
- Testing floating-point edge cases
- Exploring the floating-point number space

### previous()

```php
public static function previous(float $f): float
```

Returns the previous representable floating-point number before the given value. This performs bit-level manipulation to move to the adjacent float in the IEEE-754 number line.

**Parameters:**
- `$f` (float) - The given number

**Returns:**
- `float` - The previous floating-point number before the given number

**Behavior:**
- For positive numbers: returns a float closer to zero
- For negative numbers: returns the next smaller (more negative) float
- `+0.0` → `-0.0`
- `-PHP_FLOAT_MAX` → `-INF`
- `-INF` → `-INF`
- `INF` → `PHP_FLOAT_MAX`
- `NAN` → `NAN`

**Examples:**

```php
$f = 1.0;
$prev = Floats::previous($f);
// $prev < $f (previous representable float before 1.0)

// Navigate from positive zero to smallest negative number
$f = 0.0;
$prev = Floats::previous($f);  // -0.0
$prev2 = Floats::previous($prev);  // smallest negative float

// At the boundary
$prev = Floats::previous(-PHP_FLOAT_MAX);  // -INF
```

**Round-trip Property:**

For regular floats (not at boundaries):
```php
$f = 42.5;
Floats::next(Floats::previous($f)) === $f;  // true
Floats::previous(Floats::next($f)) === $f;  // true
```

**Use Cases:**
- Implementing interval arithmetic with tight bounds
- Generating test cases for numerical code
- Exploring floating-point precision limits

### rand()

```php
public static function rand(): float
```

Generate a random finite float by creating random bytes and unpacking them as a double. Repeatedly generates values until a non-special float is produced.

**Returns:**
- `float` - A random finite float (excludes NaN, ±INF, -0.0)

**Throws:**
- `RandomException` - If an appropriate source of randomness is unavailable

**Examples:**

```php
$f = Floats::rand();
// $f is a finite float, could be any value in the full range

// Generate 100 random floats
for ($i = 0; $i < 100; $i++) {
    $values[] = Floats::rand();
}
```

**Characteristics:**
- Produces floats with **all possible exponents** (-1022 to 1023)
- Can generate very small numbers (e.g., 1e-100) and very large numbers (e.g., 1e100)
- Much better distribution across the float space than linear methods
- Does not guarantee uniform distribution (biased by IEEE-754 representation)

**Use Cases:**
- Fuzzing and property-based testing
- Finding edge cases in floating-point algorithms
- Stress testing numerical code with diverse inputs

**Note:** This method uses `random_bytes()` to generate truly random bit patterns, then filters out special values. This means it explores the entire IEEE-754 float space, unlike range-based methods which are biased toward certain magnitudes.

### randInRange()

```php
public static function randInRange(float $min, float $max): float
```

Generate a random float uniformly distributed in the specified range using `mt_rand()`.

**Parameters:**
- `$min` (float) - The minimum value (inclusive)
- `$max` (float) - The maximum value (inclusive)

**Returns:**
- `float` - A random float in the range [min, max]

**Throws:**
- `ValueError` - If min or max are special values (NaN, ±INF, -0.0), or if min > max

**Examples:**

```php
// Random float between 0.0 and 1.0
$f = Floats::randInRange(0.0, 1.0);

// Random temperature between -10°C and 40°C
$temp = Floats::randInRange(-10.0, 40.0);

// When min equals max, returns that value
$f = Floats::randInRange(5.0, 5.0);  // 5.0
```

**Limitations:**
- Uses `mt_rand() / mt_getrandmax()` which can only produce 2^31 distinct values
- Not all floats in the range are returnable
- Uniform distribution in the numeric range, but biased by float density
- For small ranges (e.g., -1.0 to 1.0), better coverage than large ranges

**Validation:**
```php
// These throw ValueError
Floats::randInRange(NAN, 10.0);      // min is NaN
Floats::randInRange(0.0, INF);       // max is infinite
Floats::randInRange(-0.0, 10.0);     // min is negative zero
Floats::randInRange(20.0, 10.0);     // min > max
```

**Use Cases:**
- Generating test data within specific ranges
- Monte Carlo simulations
- Random sampling for statistical analysis

**Comparison with `rand()`:**
- `rand()`: Explores full float space, good for finding edge cases
- `randInRange()`: Constrained distribution, good for domain-specific testing
