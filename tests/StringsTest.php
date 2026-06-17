<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use OceanMoon\Core\Strings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * Tests for the Strings utility class.
 */
#[CoversClass(Strings::class)]
final class StringsTest extends TestCase
{
    // region toString() tests

    /**
     * Test toString with a string passes through as-is.
     */
    public function testToStringWithString(): void
    {
        $this->assertSame('hello', Strings::toString('hello'));
        $this->assertSame('', Strings::toString(''));
    }

    /**
     * Test toString with a Stringable object uses __toString().
     */
    public function testToStringWithStringableObject(): void
    {
        $obj = new class implements Stringable {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $this->assertSame('stringable object', Strings::toString($obj));
    }

    /**
     * Test toString with an integer uses Stringify.
     */
    public function testToStringWithInteger(): void
    {
        $this->assertSame('42', Strings::toString(42));
        $this->assertSame('0', Strings::toString(0));
        $this->assertSame('-7', Strings::toString(-7));
    }

    /**
     * Test toString with a float uses Stringify.
     */
    public function testToStringWithFloat(): void
    {
        $this->assertSame('3.14', Strings::toString(3.14));
        $this->assertSame('5.0', Strings::toString(5.0));
    }

    /**
     * Test toString with a boolean uses Stringify.
     */
    public function testToStringWithBoolean(): void
    {
        $this->assertSame('true', Strings::toString(true));
        $this->assertSame('false', Strings::toString(false));
    }

    /**
     * Test toString with null uses Stringify.
     */
    public function testToStringWithNull(): void
    {
        $this->assertSame('null', Strings::toString(null));
    }

    /**
     * Test toString with an array uses Stringify.
     */
    public function testToStringWithArray(): void
    {
        $this->assertSame('[1, 2, 3]', Strings::toString([1, 2, 3]));
    }

    // endregion

    // region print() tests

    /**
     * Test print outputs a string.
     */
    public function testPrintWithString(): void
    {
        $this->expectOutputString('hello');
        Strings::print('hello');
    }

    /**
     * Test print outputs a non-string value via Stringify.
     */
    public function testPrintWithNonString(): void
    {
        $this->expectOutputString('42');
        Strings::print(42);
    }

    // endregion

    // region println() tests

    /**
     * Test println outputs a string with newline.
     */
    public function testPrintlnWithString(): void
    {
        $this->expectOutputString('hello' . PHP_EOL);
        Strings::println('hello');
    }

    /**
     * Test println outputs a non-string value with newline.
     */
    public function testPrintlnWithNonString(): void
    {
        $this->expectOutputString('true' . PHP_EOL);
        Strings::println(true);
    }

    /**
     * Test println with a Stringable object.
     */
    public function testPrintlnWithStringableObject(): void
    {
        $obj = new class implements Stringable {
            public function __toString(): string
            {
                return 'from __toString';
            }
        };

        $this->expectOutputString('from __toString' . PHP_EOL);
        Strings::println($obj);
    }

    // endregion
}
