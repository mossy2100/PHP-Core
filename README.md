# OceanMoon PHP Core

A general purpose package containing core PHP utility classes and methods for use by other OceanMoon PHP packages.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)**

![PHP 8.4](docs/logo_php8_4.png)

---

## Description

This package provides a comprehensive set of utility classes for working with various PHP types and common operations.
All utility classes are final with static methods and cannot be instantiated.

---

## Development and Quality Assurance

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the
development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude
providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and
documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including
[PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and
[PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/)
coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach has produced a
well-designed, production-ready package with thorough test coverage and documentation.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---

## Requirements

- PHP ^8.4

---

## Installation

```bash
composer require oceanmoon/core
```

---

## Classes

### [Floats](docs/Floats.md)

Float-specific utilities for handling IEEE-754 special values (-0.0, ±INF, NAN), approximate comparison, float space
navigation (next/previous), random generation, and IEEE-754 component assembly/disassembly.

### [Integers](docs/Integers.md)

Integer arithmetic operations with overflow checking (add, subtract, multiply, power), GCD calculation, and Unicode
subscript/superscript conversion.

### [Arrays](docs/Arrays.md)

Array utility methods including circular reference detection, value quoting, and first/last element extraction.

### [Types](docs/Types.md)

Type checking and inspection utilities with methods for identifying numbers, unsigned integers, traits, and generating
unique string keys.

### [Stringify](docs/Stringify.md)

Advanced value-to-string conversion with pretty printing, supporting all PHP types with improved readability. This class
serves as the basis of the `inspect()` function.

---

## Globals

### [Constants](docs/Globals/Constants.md)

Useful constants used by the Core, Math, and other packages, including `M_TAU`.

### [Strings](docs/Globals/Strings.md)

Convenient functions for outputting strings and other values, including `inspect()`, `write()` and `writeln()`.

### [Numbers](docs/Globals/Numbers.md)

Convenient functions for working with numbers, including `is_number()` , `is_zero()`, `sign()`, and `copy_sign()`.

---

## Traits

### Comparison Traits

Equality and ordering comparison operations for custom types. See
[ComparisonTraits.md](docs/Traits/Comparison/ComparisonTraits.md) for the trait hierarchy and usage guide.

| Trait                                                          | Description                                                          |
| -------------------------------------------------------------- | -------------------------------------------------------------------- |
| [Equatable](docs/Traits/Comparison/Equatable.md)               | Base trait for exact equality comparison via `equal()`.              |
| [Comparable](docs/Traits/Comparison/Comparable.md)             | Extends Equatable with ordering: `lessThan()`, `greaterThan()`, etc. |
| [ApproxEquatable](docs/Traits/Comparison/ApproxEquatable.md)   | Extends Equatable with tolerance-based `approxEqual()`.              |
| [ApproxComparable](docs/Traits/Comparison/ApproxComparable.md) | Combines Comparable and ApproxEquatable with `approxCompare()`.      |

### Assert Traits

Custom PHPUnit assertions for test cases. See [TestingTraits.md](docs/Traits/Asserts/TestingTraits.md) for usage
examples.

| Trait                                                     | Description                                                                                |
| --------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| [FloatAssertions](docs/Traits/Asserts/FloatAssertions.md) | Provides `assertApproxEqual()` and `assertApproxZero()` with informative failure messages. |

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
