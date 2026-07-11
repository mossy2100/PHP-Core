<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use ArgumentCountError;
use DomainException;
use OceanMoon\Core\Exceptions\FormatException;
use OceanMoon\Core\Integers;
use OverflowException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Integers utility class.
 */
#[CoversClass(Integers::class)]
final class IntegersTest extends TestCase
{
    #region Tests for add()

    /**
     * Test addition of integers without overflow.
     */
    public function testAdd(): void
    {
        // Test basic addition.
        $this->assertSame(5, Integers::add(2, 3));
        $this->assertSame(0, Integers::add(0, 0));

        // Test addition with negative numbers.
        $this->assertSame(-5, Integers::add(-2, -3));
        $this->assertSame(1, Integers::add(-2, 3));
        $this->assertSame(-1, Integers::add(2, -3));

        // Test addition with zero.
        $this->assertSame(10, Integers::add(10, 0));
        $this->assertSame(-10, Integers::add(0, -10));

        // Test large numbers that don't overflow.
        $this->assertSame(1000000, Integers::add(500000, 500000));
    }

    /**
     * Test addition overflow detection.
     */
    public function testAddOverflow(): void
    {
        // Test positive overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer addition.');
        Integers::add(PHP_INT_MAX, 1);
    }

    /**
     * Test addition negative overflow detection.
     */
    public function testAddNegativeOverflow(): void
    {
        // Test negative overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer addition.');
        Integers::add(PHP_INT_MIN, -1);
    }

    #endregion

    #region Tests for sub()

    /**
     * Test subtraction of integers without overflow.
     */
    public function testSub(): void
    {
        // Test basic subtraction.
        $this->assertSame(1, Integers::sub(3, 2));
        $this->assertSame(0, Integers::sub(0, 0));

        // Test subtraction with negative numbers.
        $this->assertSame(1, Integers::sub(-2, -3));
        $this->assertSame(-5, Integers::sub(-2, 3));
        $this->assertSame(5, Integers::sub(2, -3));

        // Test subtraction with zero.
        $this->assertSame(10, Integers::sub(10, 0));
        $this->assertSame(-10, Integers::sub(0, 10));

        // Test large numbers that don't overflow.
        $this->assertSame(0, Integers::sub(500000, 500000));
    }

    /**
     * Test subtraction overflow detection.
     */
    public function testSubOverflow(): void
    {
        // Test positive overflow (subtracting a large negative number).
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer subtraction.');
        Integers::sub(PHP_INT_MAX, -1);
    }

    /**
     * Test subtraction negative overflow detection.
     */
    public function testSubNegativeOverflow(): void
    {
        // Test negative overflow (subtracting a large positive number from minimum).
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer subtraction.');
        Integers::sub(PHP_INT_MIN, 1);
    }

    #endregion

    #region Tests for mul()

    /**
     * Test multiplication of integers without overflow.
     */
    public function testMul(): void
    {
        // Test basic multiplication.
        $this->assertSame(6, Integers::mul(2, 3));
        $this->assertSame(0, Integers::mul(0, 0));

        // Test multiplication with negative numbers.
        $this->assertSame(6, Integers::mul(-2, -3));
        $this->assertSame(-6, Integers::mul(-2, 3));
        $this->assertSame(-6, Integers::mul(2, -3));

        // Test multiplication with zero.
        $this->assertSame(0, Integers::mul(10, 0));
        $this->assertSame(0, Integers::mul(0, 10));

        // Test multiplication with one.
        $this->assertSame(10, Integers::mul(10, 1));
        $this->assertSame(-10, Integers::mul(-10, 1));

        // Test large numbers that don't overflow.
        $this->assertSame(1000000, Integers::mul(1000, 1000));
    }

    /**
     * Test multiplication overflow detection.
     */
    public function testMulOverflow(): void
    {
        // Test positive overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer multiplication.');
        Integers::mul(PHP_INT_MAX, 2);
    }

    /**
     * Test multiplication negative overflow detection.
     */
    public function testMulNegativeOverflow(): void
    {
        // Test negative overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer multiplication.');
        Integers::mul(PHP_INT_MAX, -2);
    }

    #endregion

    #region Tests for pow()

    /**
     * Test exponentiation of integers without overflow.
     */
    public function testPow(): void
    {
        // Test basic exponentiation.
        $this->assertSame(8, Integers::pow(2, 3));
        $this->assertSame(1, Integers::pow(5, 0));
        $this->assertSame(5, Integers::pow(5, 1));

        // Test with zero base.
        $this->assertSame(0, Integers::pow(0, 5));
        $this->assertSame(1, Integers::pow(0, 0));

        // Test with negative base.
        $this->assertSame(-8, Integers::pow(-2, 3));
        $this->assertSame(16, Integers::pow(-2, 4));
        $this->assertSame(1, Integers::pow(-5, 0));

        // Test with one.
        $this->assertSame(1, Integers::pow(1, 100));
        $this->assertSame(1, Integers::pow(-1, 0));
        $this->assertSame(-1, Integers::pow(-1, 1));

        // Test larger calculations that don't overflow.
        $this->assertSame(1024, Integers::pow(2, 10));
    }

    /**
     * Test exponentiation with negative exponent causing underflow.
     */
    public function testPowNegativeExponentUnderflow(): void
    {
        // Test that negative exponents throw DomainException.
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid exponent: -1. Must not be negative.');
        Integers::pow(2, -1);
    }

    /**
     * Test exponentiation overflow detection.
     */
    public function testPowOverflow(): void
    {
        // Test positive overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in exponentiation.');
        Integers::pow(PHP_INT_MAX, 2);
    }

    /**
     * Test exponentiation with large exponent causing overflow.
     */
    public function testPowLargeExponentOverflow(): void
    {
        // Test overflow with large exponent.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in exponentiation.');
        Integers::pow(10, 100);
    }

    #endregion

    #region Tests for gcd()

    /**
     * Test GCD calculation with two integers.
     */
    public function testGcdTwoIntegers(): void
    {
        // Test basic GCD.
        $this->assertSame(6, Integers::gcd(12, 18));
        $this->assertSame(1, Integers::gcd(17, 19));
        $this->assertSame(5, Integers::gcd(5, 10));

        // Test with same numbers.
        $this->assertSame(7, Integers::gcd(7, 7));

        // Test with one being zero.
        $this->assertSame(5, Integers::gcd(5, 0));
        $this->assertSame(5, Integers::gcd(0, 5));

        // Test with both being zero.
        $this->assertSame(0, Integers::gcd(0, 0));

        // Test with negative numbers (GCD uses absolute values).
        $this->assertSame(6, Integers::gcd(-12, 18));
        $this->assertSame(6, Integers::gcd(12, -18));
        $this->assertSame(6, Integers::gcd(-12, -18));

        // Test with one being one.
        $this->assertSame(1, Integers::gcd(1, 100));
    }

    /**
     * Test GCD calculation with multiple integers.
     */
    public function testGcdMultipleIntegers(): void
    {
        // Test with three integers.
        $this->assertSame(6, Integers::gcd(12, 18, 24));
        $this->assertSame(1, Integers::gcd(10, 15, 22));

        // Test with four integers.
        $this->assertSame(4, Integers::gcd(8, 12, 16, 20));

        // Test with five integers.
        $this->assertSame(5, Integers::gcd(10, 15, 20, 25, 30));

        // Test with mixed positive and negative.
        $this->assertSame(3, Integers::gcd(-9, 12, -15));
    }

    /**
     * Test GCD calculation with single integer.
     */
    public function testGcdSingleInteger(): void
    {
        // Test with single positive integer.
        $this->assertSame(42, Integers::gcd(42));

        // Test with single negative integer.
        $this->assertSame(42, Integers::gcd(-42));

        // Test with zero.
        $this->assertSame(0, Integers::gcd(0));
    }

    /**
     * Test GCD with no arguments throws error.
     */
    public function testGcdNoArguments(): void
    {
        // Test that calling GCD with no arguments throws ArgumentCountError.
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('At least one integer is required.');
        Integers::gcd();
    }

    /**
     * Test GCD with large numbers.
     */
    public function testGcdLargeNumbers(): void
    {
        // Test GCD with large coprime numbers.
        $this->assertSame(1, Integers::gcd(1000000007, 1000000009));

        // Test GCD with large numbers having common factors.
        $this->assertSame(3000, Integers::gcd(123000, 456000));
    }

    /**
     * Test GCD with PHP_INT_MIN as first argument throws RangeException.
     */
    public function testGcdWithPhpIntMinFirstArgThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot compute GCD with PHP_INT_MIN');
        Integers::gcd(PHP_INT_MIN, 5);
    }

    /**
     * Test GCD with PHP_INT_MIN as second argument throws RangeException.
     */
    public function testGcdWithPhpIntMinSecondArgThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot compute GCD with PHP_INT_MIN');
        Integers::gcd(5, PHP_INT_MIN);
    }

    /**
     * Test GCD with PHP_INT_MIN as only argument throws RangeException.
     */
    public function testGcdWithPhpIntMinSingleArgThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot compute GCD with PHP_INT_MIN');
        Integers::gcd(PHP_INT_MIN);
    }

    #endregion

    #region Tests for toSubscript()

    /**
     * Test toSubscript with positive integer.
     */
    public function testToSubscriptPositive(): void
    {
        $this->assertSame('₁₂₃', Integers::toSubscript(123));
        $this->assertSame('₀', Integers::toSubscript(0));
        $this->assertSame('₉₈₇₆₅₄₃₂₁₀', Integers::toSubscript(9876543210));
    }

    /**
     * Test toSubscript with negative integer.
     */
    public function testToSubscriptNegative(): void
    {
        $this->assertSame('₋₁₂₃', Integers::toSubscript(-123));
        $this->assertSame('₋₁', Integers::toSubscript(-1));
    }

    #endregion

    #region Tests for toSuperscript()

    /**
     * Test toSuperscript with positive integer.
     */
    public function testToSuperscriptPositive(): void
    {
        $this->assertSame('¹²³', Integers::toSuperscript(123));
        $this->assertSame('⁰', Integers::toSuperscript(0));
        $this->assertSame('⁹⁸⁷⁶⁵⁴³²¹⁰', Integers::toSuperscript(9876543210));
    }

    /**
     * Test toSuperscript with negative integer.
     */
    public function testToSuperscriptNegative(): void
    {
        $this->assertSame('⁻¹²³', Integers::toSuperscript(-123));
        $this->assertSame('⁻¹', Integers::toSuperscript(-1));
    }

    #endregion

    #region Tests for isSubscript()

    /**
     * Test isSubscript with valid subscript strings.
     */
    public function testIsSubscriptValid(): void
    {
        // Positive integers.
        $this->assertTrue(Integers::isSubscript('₁₂₃'));
        $this->assertTrue(Integers::isSubscript('₀'));
        $this->assertTrue(Integers::isSubscript('₉₈₇₆₅₄₃₂₁₀'));

        // Negative integers.
        $this->assertTrue(Integers::isSubscript('₋₁₂₃'));
        $this->assertTrue(Integers::isSubscript('₋₁'));
        $this->assertTrue(Integers::isSubscript('₋₀'));
    }

    /**
     * Test isSubscript with invalid strings.
     */
    public function testIsSubscriptInvalid(): void
    {
        // Empty string.
        $this->assertFalse(Integers::isSubscript(''));

        // Regular digits.
        $this->assertFalse(Integers::isSubscript('123'));
        $this->assertFalse(Integers::isSubscript('-123'));

        // Superscript characters.
        $this->assertFalse(Integers::isSubscript('¹²³'));

        // Mixed subscript and regular.
        $this->assertFalse(Integers::isSubscript('₁2₃'));

        // Mixed subscript and superscript.
        $this->assertFalse(Integers::isSubscript('₁²₃'));

        // Just minus sign.
        $this->assertFalse(Integers::isSubscript('₋'));

        // Letters.
        $this->assertFalse(Integers::isSubscript('abc'));

        // Minus sign in wrong position.
        $this->assertFalse(Integers::isSubscript('₁₋₂'));
    }

    #endregion

    #region Tests for isSuperscript()

    /**
     * Test isSuperscript with valid superscript strings.
     */
    public function testIsSuperscriptValid(): void
    {
        // Positive integers.
        $this->assertTrue(Integers::isSuperscript('¹²³'));
        $this->assertTrue(Integers::isSuperscript('⁰'));
        $this->assertTrue(Integers::isSuperscript('⁹⁸⁷⁶⁵⁴³²¹⁰'));

        // Negative integers.
        $this->assertTrue(Integers::isSuperscript('⁻¹²³'));
        $this->assertTrue(Integers::isSuperscript('⁻¹'));
        $this->assertTrue(Integers::isSuperscript('⁻⁰'));
    }

    /**
     * Test isSuperscript with invalid strings.
     */
    public function testIsSuperscriptInvalid(): void
    {
        // Empty string.
        $this->assertFalse(Integers::isSuperscript(''));

        // Regular digits.
        $this->assertFalse(Integers::isSuperscript('123'));
        $this->assertFalse(Integers::isSuperscript('-123'));

        // Subscript characters.
        $this->assertFalse(Integers::isSuperscript('₁₂₃'));

        // Mixed superscript and regular.
        $this->assertFalse(Integers::isSuperscript('¹2³'));

        // Mixed superscript and subscript.
        $this->assertFalse(Integers::isSuperscript('¹₂³'));

        // Just minus sign.
        $this->assertFalse(Integers::isSuperscript('⁻'));

        // Letters.
        $this->assertFalse(Integers::isSuperscript('abc'));

        // Minus sign in wrong position.
        $this->assertFalse(Integers::isSuperscript('¹⁻²'));
    }

    #endregion

    #region Tests for fromSubscript()

    /**
     * Test fromSubscript with valid subscript strings.
     */
    public function testFromSubscriptValid(): void
    {
        // Positive integers.
        $this->assertSame(123, Integers::fromSubscript('₁₂₃'));
        $this->assertSame(0, Integers::fromSubscript('₀'));
        $this->assertSame(9876543210, Integers::fromSubscript('₉₈₇₆₅₄₃₂₁₀'));

        // Negative integers.
        $this->assertSame(-123, Integers::fromSubscript('₋₁₂₃'));
        $this->assertSame(-1, Integers::fromSubscript('₋₁'));

        // Single digit.
        $this->assertSame(5, Integers::fromSubscript('₅'));
    }

    /**
     * Test fromSubscript throws exception for invalid characters.
     */
    public function testFromSubscriptInvalidCharacter(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid subscript character');
        Integers::fromSubscript('₁2₃');
    }

    /**
     * Test fromSubscript throws exception for superscript characters.
     */
    public function testFromSubscriptSuperscriptCharacter(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid subscript character');
        Integers::fromSubscript('¹²³');
    }

    /**
     * Test fromSubscript throws exception for regular digits.
     */
    public function testFromSubscriptRegularDigits(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid subscript character');
        Integers::fromSubscript('123');
    }

    #endregion

    #region Tests for fromSuperscript()

    /**
     * Test fromSuperscript with valid superscript strings.
     */
    public function testFromSuperscriptValid(): void
    {
        // Positive integers.
        $this->assertSame(123, Integers::fromSuperscript('¹²³'));
        $this->assertSame(0, Integers::fromSuperscript('⁰'));
        $this->assertSame(9876543210, Integers::fromSuperscript('⁹⁸⁷⁶⁵⁴³²¹⁰'));

        // Negative integers.
        $this->assertSame(-123, Integers::fromSuperscript('⁻¹²³'));
        $this->assertSame(-1, Integers::fromSuperscript('⁻¹'));

        // Single digit.
        $this->assertSame(5, Integers::fromSuperscript('⁵'));
    }

    /**
     * Test fromSuperscript throws exception for invalid characters.
     */
    public function testFromSuperscriptInvalidCharacter(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid superscript character');
        Integers::fromSuperscript('¹2³');
    }

    /**
     * Test fromSuperscript throws exception for subscript characters.
     */
    public function testFromSuperscriptSubscriptCharacter(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid superscript character');
        Integers::fromSuperscript('₁₂₃');
    }

    /**
     * Test fromSuperscript throws exception for regular digits.
     */
    public function testFromSuperscriptRegularDigits(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage('Invalid superscript character');
        Integers::fromSuperscript('123');
    }

    #endregion

    #region Round-trip tests

    /**
     * Test round-trip conversion: toSubscript then fromSubscript.
     */
    public function testSubscriptRoundTrip(): void
    {
        $values = [0, 1, -1, 123, -456, 9876543210];
        foreach ($values as $value) {
            $subscript = Integers::toSubscript($value);
            $result = Integers::fromSubscript($subscript);
            $this->assertSame($value, $result);
        }
    }

    /**
     * Test round-trip conversion: toSuperscript then fromSuperscript.
     */
    public function testSuperscriptRoundTrip(): void
    {
        $values = [0, 1, -1, 123, -456, 9876543210];
        foreach ($values as $value) {
            $superscript = Integers::toSuperscript($value);
            $result = Integers::fromSuperscript($superscript);
            $this->assertSame($value, $result);
        }
    }

    #endregion
}
