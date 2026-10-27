# Mossy2100 PHP Core

A general purpose package containing core PHP utility classes and methods for use by other Mossy2100 PHP packages.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)**

![PHP 8.4](docs/logo_php8_4.png)

---

## Description

This package provides a comprehensive set of utility classes for working with various PHP types and common operations.
Most utility classes are final with static methods and cannot be instantiated directly. `Console` is the exception - an
instantiable class with instance methods and internal style state, since its fluent `->` chaining API needs a real
object to chain off.

---

## Development and Quality Assurance

This package was developed with [Claude Code](https://claude.com/product/claude-code):

- The author developed the initial concept, designed the public API, and did most of the initial coding.
- Claude Code wrote the tests and most of the documentation.
- Claude Code also assisted with multiple code reviews, which led to bug fixes, performance improvements and other
  refinements.

The code is checked with:

- [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), for [PSR-12](https://www.php-fig.org/psr/psr-12/)
  compliance plus the Mossy2100 coding standard
- [PHPStan](https://phpstan.org/), at level 9
- [PHPUnit](https://phpunit.de/index.html), with 100% code coverage

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---

## Requirements

- PHP ^8.4
- The `intl` extension, for `NumberFormatter` in `Floats::format()`
- The `mbstring` extension
- PHPUnit, only if you use the `FloatAssertions` trait in your tests

---

## Installation

```bash
composer require mossy2100/core
```

---

## Quick Start

```php
use Mossy2100\Core\Floats;
use Mossy2100\Core\Integers;
use Mossy2100\Core\Stringify;

Floats::approxEqual(0.1 + 0.2, 0.3);               // true
Floats::format(0.0001234);                         // '1.234×10⁻⁴'
Integers::mul(PHP_INT_MAX, 2);                     // throws OverflowException
Integers::toSuperscript(-42);                      // '⁻⁴²'
Stringify::stringify(['a' => 1.0, 'b' => [1, 2]]); // "['a' => 1.0, 'b' => [1, 2]]"
```

See [Classes](#classes) below for what each class does, with links to its full documentation.

---

## Classes

### [Floats](docs/Floats.md)

Float-specific utilities for handling IEEE-754 special values (-0.0, ±INF, NAN), approximate comparison, float space
navigation (nextUp/nextDown), random generation, and IEEE-754 component assembly/disassembly.

### [Integers](docs/Integers.md)

Integer arithmetic with overflow checking (add, subtract, multiply, power), overflow-safe modular multiplication
(`mulMod()`), GCD calculation, and Unicode subscript/superscript conversion.

### [Numbers](docs/Numbers.md)

Number-related utilities for type checking (distinguishing actual numbers from numeric strings), exact equality that
correctly handles mixed int/float comparisons, and sign operations with IEEE-754 signed zero support.

### [Arrays](docs/Arrays.md)

Array utility methods: value quoting, serial lists (`'a, b, and c'`), first/last element extraction, value removal, and
circular reference removal.

### [Types](docs/Types.md)

Type inspection utilities: canonical basic type names, unique string keys for collections, and trait detection,
including inherited and nested traits.

### [Stringify](docs/Stringify.md)

Advanced value-to-string conversion with pretty printing, supporting all PHP types with improved readability. This class
serves as the basis of the `Console::dump()` method.

### [StringifyOptions](docs/StringifyOptions.md)

Per-call options for `Stringify`: pretty printing, indent and maximum line length.

### [Environment](docs/Environment.md)

Runtime environment detection: 64-bit architecture checks, and the invariant locale used for number formatting.

### [Console](docs/Console.md)

ANSI/SGR-styled console output: 16-color foreground/background, text attributes (bold, italic, underline, etc.), method
chaining, style snapshot/restore, and higher-level helpers like `dump()` and severity-colored messages (`success()`,
`error()`, `warn()`, `info()`).

---

## [Globals](docs/Globals.md)

Useful constants (`M_TAU`, `RECURSION`) that are more convenient to use as globals (namespaced to `Mossy2100\Core`) than
as class members.

---

## Traits

### Comparison Traits

Equality and ordering comparison operations for custom types. See [ComparisonTraits.md](docs/Traits/Comparison/ComparisonTraits.md) for the trait hierarchy and usage
guide.

| Trait                                                          | Description                                                          |
| -------------------------------------------------------------- | -------------------------------------------------------------------- |
| [Equatable](docs/Traits/Comparison/Equatable.md)               | Base trait for exact equality comparison via `equal()`.              |
| [Comparable](docs/Traits/Comparison/Comparable.md)             | Extends Equatable with ordering: `lessThan()`, `greaterThan()`, etc. |
| [ApproxEquatable](docs/Traits/Comparison/ApproxEquatable.md)   | Extends Equatable with tolerance-based `approxEqual()`.              |
| [ApproxComparable](docs/Traits/Comparison/ApproxComparable.md) | Combines Comparable and ApproxEquatable with `approxCompare()`.      |

### Assert Traits

Custom PHPUnit assertions for test cases. See [TestingTraits.md](docs/Traits/Asserts/TestingTraits.md) for usage examples.

| Trait                                                     | Description                                                       |
| --------------------------------------------------------- | ----------------------------------------------------------------- |
| [FloatAssertions](docs/Traits/Asserts/FloatAssertions.md) | Provides `assertApproxEqual()` with informative failure messages. |

---

## Enums

### [FloatFormat](docs/Enums/FloatFormat.md)

Notation styles for `Floats::format()` - fixed-point, scientific, or automatic selection between the two.

### [ExponentFormat](docs/Enums/ExponentFormat.md)

Rendering styles for the exponent portion of scientific-notation float output, from plain ASCII `e`/`E` through to
Unicode and HTML mathematical notation.

---

## Exceptions

### [FormatException](docs/Exceptions/FormatException.md)

Exception thrown when a string has an invalid format for the desired operation. Extends `DomainException`. Useful for
parse methods or constructors that accept string arguments.

### [ArithmeticException](docs/Exceptions/ArithmeticException.md)

Exception thrown when an arithmetic operation has no defined result for the given operands, such as division by zero or
a logarithm of a non-positive number. Extends `DomainException`. Displaces `DivisionByZeroError` for userland arithmetic
code in value types like `Complex`, `Rational`, `Vector`, and `Matrix`.

---

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test class
vendor/bin/phpunit tests/NumbersTest.php

# Run with coverage (generates HTML report and clover.xml)
composer test
```

---

## License

MIT License - see [LICENSE](LICENSE) for details

---

## Support

- **Issues**: https://github.com/mossy2100/PHP-Core/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Core/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
