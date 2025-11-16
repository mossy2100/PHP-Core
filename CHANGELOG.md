# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-01-16

### Added
- `Angle` class for working with angles in radians and degrees
- `Floats` class with utility methods for floating-point operations
- `Integers` class with utility methods for integer operations
- `Stringify` class for converting values to strings
- `Types` class with utility methods for type checking and manipulation
  - `isNumber()` - Check if value is int or float
  - `isUint()` - Check if value is unsigned integer
  - `getBasicType()` - Get canonical type name
  - `getUniqueString()` - Convert any value to unique string
  - `createError()` - Create TypeError with helpful message
  - `usesTrait()` - Check if class/object uses a trait
  - `getTraits()` - Get all traits used by class/interface/trait

### Requirements
- PHP ^8.4
- Removed ext-ctype and ext-mbstring (not directly used)

### Development
- PSR-12 coding standards
- PHPStan level 9 static analysis
- PHPUnit test coverage
- Comprehensive test suite with edge case testing
