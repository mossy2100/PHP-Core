# Galaxon PHP Core

A general purpose library containing core utility classes and methods for use by other Galaxon PHP packages.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Coverage Report](https://html-preview.github.io/?url=https://github.com/mossy2100/PHP-Core/blob/main/build/coverage/index.html)**

![PHP 8.4](docs/logo_php8_4.png)

## Description

This package provides a comprehensive set of utility classes for working with various PHP types and common operations. All utility classes are final with static methods and cannot be instantiated.

## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a high-quality, thoroughly-tested, and well-documented package delivered in significantly less time than traditional development methods.

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

### [Equatable](docs/Equatable.md)

Interface for objects that can be compared for equality. Provides a single `equals()` method for type-safe equality checks.

### [Comparable](docs/Comparable.md)

Trait providing a complete set of comparison operations (`equals()`, `isLessThan()`, `isGreaterThan()`, etc.) based on a single `compare()` method.

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test class
vendor/bin/phpunit tests/Floats.php

# Run with coverage (generates HTML report and clover.xml)
composer test
```

## License

MIT License - see [LICENSE](LICENSE) for details

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

For questions or suggestions, please  [open an issue](https://github.com/mossy2100/PHP-Core/issues).

## Support

- **Issues**: https://github.com/mossy2100/PHP-Core/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
