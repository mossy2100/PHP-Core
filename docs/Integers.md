# Integers

Container for useful integer-related methods with overflow checking.

## Methods

### add()

```php
public static function add(int $a, int $b): int
```

Add two integers with overflow check. Throws `OverflowException` if overflow occurs.

### sub()

```php
public static function sub(int $a, int $b): int
```

Subtract one integer from another with overflow check. Throws `OverflowException` if overflow occurs.

### mul()

```php
public static function mul(int $a, int $b): int
```

Multiply two integers with overflow check. Throws `OverflowException` if overflow occurs.

### pow()

```php
public static function pow(int $a, int $b): int
```

Raise one integer to the power of another with overflow check. Requires `$b` to be non-negative. Throws `ValueError` for negative exponents, `OverflowException` if overflow occurs.

### gcd()

```php
public static function gcd(int ...$nums): int
```

Calculate the greatest common divisor of two or more integers using Euclid's algorithm. Throws `ArgumentCountError` if no arguments are provided.
