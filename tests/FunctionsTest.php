<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use PHPUnit\Framework\TestCase;

use function OceanMoon\Core\println;

/**
 * Tests for the convenience functions in functions.php.
 */
final class FunctionsTest extends TestCase
{
    // region println() tests

    /**
     * Test println outputs a string with a newline.
     */
    public function testPrintlnWithString(): void
    {
        $this->expectOutputString('Hello' . PHP_EOL);
        println('Hello');
    }

    /**
     * Test println outputs an integer with a newline.
     */
    public function testPrintlnWithInt(): void
    {
        $this->expectOutputString('42' . PHP_EOL);
        println(42);
    }

    /**
     * Test println outputs a float with a newline.
     */
    public function testPrintlnWithFloat(): void
    {
        $this->expectOutputString('3.14' . PHP_EOL);
        println(3.14);
    }

    /**
     * Test println with no argument outputs just a newline.
     */
    public function testPrintlnWithNoArgument(): void
    {
        $this->expectOutputString(PHP_EOL);
        println();
    }

    /**
     * Test println with an empty string outputs just a newline.
     */
    public function testPrintlnWithEmptyString(): void
    {
        $this->expectOutputString(PHP_EOL);
        println('');
    }

    /**
     * Test println with a boolean true.
     */
    public function testPrintlnWithTrue(): void
    {
        $this->expectOutputString('true' . PHP_EOL);
        println(true);
    }

    /**
     * Test println with a boolean false.
     */
    public function testPrintlnWithFalse(): void
    {
        $this->expectOutputString('false' . PHP_EOL);
        println(false);
    }

    /**
     * Test println with null.
     */
    public function testPrintlnWithNull(): void
    {
        $this->expectOutputString('null' . PHP_EOL);
        println(null);
    }

    /**
     * Test println with an object that has a __toString() method.
     */
    public function testPrintlnWithStringableObject(): void
    {
        $obj = new class {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $this->expectOutputString('stringable object' . PHP_EOL);
        println($obj);
    }

    // endregion
}
