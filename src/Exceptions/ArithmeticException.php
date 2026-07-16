<?php

declare(strict_types=1);

namespace OceanMoon\Core\Exceptions;

use DomainException;

/**
 * Exception thrown when an arithmetic operation has no defined result for the given operands.
 *
 * Covers cases like division by zero, logarithm of a non-positive number or with base 0 or 1, and other operations
 * that are undefined for specific inputs. This is the checked-exception equivalent of what a float would signal by
 * returning NAN or ±INF: value types like Complex, Rational, Vector, and Matrix have no such sentinel to fall back
 * on, so they throw instead.
 *
 * Use this exception instead of DivisionByZeroError. "Error" types should in principle never be thrown from userland
 * code.
 *
 * This exception should not be confused with PHP's built-in ArithmeticError, which signals engine-level conditions
 * (e.g. arithmetic overflow) rather than a recoverable domain-level operation failure.
 */
class ArithmeticException extends DomainException
{
}
