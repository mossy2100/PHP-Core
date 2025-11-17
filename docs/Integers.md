# Integers

Integer arithmetic operations with overflow checking and greatest common divisor calculation.

## Background

In PHP, when integer arithmetic operations exceed `PHP_INT_MAX` or fall below `PHP_INT_MIN`, the result is silently converted to a float. This can lead to unexpected behavior in calculations that should produce integers.

This class provides integer arithmetic methods that detect overflow and throw exceptions instead of silently converting to float. This is useful when you need to ensure results remain within the integer range or handle overflow explicitly.

## Methods

### add()

```php
public static function add(int $a, int $b): int
```

Add two integers with overflow detection.

**Parameters:**
- `$a` (int) - The first integer
- `$b` (int) - The second integer

**Returns:**
- `int` - The sum of the two integers

**Throws:**
- `OverflowException` - If the addition results in overflow

**Examples:**

```php
Integers::add(5, 3);           // 8
Integers::add(-10, 15);        // 5
Integers::add(PHP_INT_MAX, 1); // throws OverflowException
```

### sub()

```php
public static function sub(int $a, int $b): int
```

Subtract one integer from another with overflow detection.

**Parameters:**
- `$a` (int) - The integer to subtract from
- `$b` (int) - The integer to subtract

**Returns:**
- `int` - The difference (a - b)

**Throws:**
- `OverflowException` - If the subtraction results in overflow

**Examples:**

```php
Integers::sub(10, 3);           // 7
Integers::sub(5, -5);           // 10
Integers::sub(PHP_INT_MIN, 1);  // throws OverflowException
```

### mul()

```php
public static function mul(int $a, int $b): int
```

Multiply two integers with overflow detection.

**Parameters:**
- `$a` (int) - The first integer
- `$b` (int) - The second integer

**Returns:**
- `int` - The product

**Throws:**
- `OverflowException` - If the multiplication results in overflow

**Examples:**

```php
Integers::mul(6, 7);            // 42
Integers::mul(-3, 4);           // -12
Integers::mul(PHP_INT_MAX, 2);  // throws OverflowException
```

### pow()

```php
public static function pow(int $a, int $b): int
```

Raise one integer to the power of another with overflow detection. Only non-negative exponents are supported.

**Parameters:**
- `$a` (int) - The base
- `$b` (int) - The exponent (must be non-negative)

**Returns:**
- `int` - The result of raising a to the power of b

**Throws:**
- `ValueError` - If the exponent is negative
- `OverflowException` - If the exponentiation results in overflow

**Examples:**

```php
Integers::pow(2, 10);            // 1024
Integers::pow(5, 0);             // 1
Integers::pow(-2, 3);            // -8
Integers::pow(2, -1);            // throws ValueError
Integers::pow(10, 100);          // throws OverflowException
```

**Note:** Negative exponents are not supported because they would produce non-integer results (e.g., 2^-1 = 0.5).

### gcd()

```php
public static function gcd(int ...$nums): int
```

Calculate the greatest common divisor (GCD) of two or more integers using Euclid's algorithm. The GCD is the largest positive integer that divides all the given numbers without remainder.

**Parameters:**
- `...$nums` (int) - Two or more integers

**Returns:**
- `int` - The greatest common divisor (always non-negative)

**Throws:**
- `ArgumentCountError` - If no arguments are provided

**Examples:**

```php
Integers::gcd(12, 18);          // 6
Integers::gcd(17, 19);          // 1 (coprime)
Integers::gcd(12, 18, 24);      // 6
Integers::gcd(-12, 18);         // 6 (uses absolute values)
Integers::gcd(0, 5);            // 5
Integers::gcd(0, 0);            // 0
```

**Note:** The GCD is always computed using absolute values, so negative inputs are treated as positive. The GCD of 0 and any number n is |n|. The GCD of 0 and 0 is 0.
