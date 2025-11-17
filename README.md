# Galaxon Core

A general purpose library containing core utility classes and methods for use by other Galaxon PHP packages.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Coverage Report](https://html-preview.github.io/?url=https://github.com/mossy2100/PHP-Core/blob/main/build/coverage/index.html)**

![PHP 8.4](docs/logo_php8_4.png)

## Description

This package provides a comprehensive set of utility classes for working with various PHP types and common operations. All utility classes are final with static methods and cannot be instantiated.

## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a high-quality, thoroughly-tested package delivered in significantly less time than traditional development methods.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

## Requirements

- PHP ^8.4

## Installation

```bash
composer require galaxon/core
```

## Classes

### [Numbers](docs/Numbers.md)

General number-related utility methods including sign operations.

### [Floats](docs/Floats.md)

Float-specific utilities for handling special values like -0.0, ±INF, NaN, and hexadecimal conversion.

### [Integers](docs/Integers.md)

Integer arithmetic operations with overflow checking (add, subtract, multiply, power) and GCD calculation.

### [Arrays](docs/Arrays.md)

Array utility methods including circular reference detection.

### [Types](docs/Types.md)

Type checking and inspection utilities with methods for identifying numbers, unsigned integers, traits, and generating unique string keys.

### [Stringify](docs/Stringify.md)

Advanced value-to-string conversion with pretty printing, supporting all PHP types with improved readability.

### [Angle](docs/Angle.md)

Immutable angle class with support for multiple units (radians, degrees, gradians, turns, DMS), trigonometry, and arithmetic operations.

## Testing

Run the test suite:

```bash
composer test
```

For code coverage analysis:

```bash
composer test-coverage
```

## Contributing

Please report bugs and feature requests on [GitHub Issues](https://github.com/mossy2100/Galaxon/issues).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
