<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use PHPUnit\Framework\TestCase;

use function OceanMoon\Core\write;
use function OceanMoon\Core\writeln;

/**
 * Tests for the convenience functions in functions.php.
 */
final class FunctionsTest extends TestCase
{
    #region write() tests

    /**
     * Test write outputs a string with a newline.
     */
    public function testWriteWithString(): void
    {
        $this->expectOutputString('Hello');
        write('Hello');
    }

    /**
     * Test write outputs an integer with a newline.
     */
    public function testWriteWithInt(): void
    {
        $this->expectOutputString('42');
        write(42);
    }

    /**
     * Test write outputs a float with a newline.
     */
    public function testWriteWithFloat(): void
    {
        $this->expectOutputString('3.14');
        write(3.14);
    }

    /**
     * Test write with no argument outputs nothing.
     */
    public function testWriteWithNoArgument(): void
    {
        $this->expectOutputString('');
        write();
    }

    /**
     * Test write with an empty string outputs nothing.
     */
    public function testWriteWithEmptyString(): void
    {
        $this->expectOutputString('');
        write('');
    }

    /**
     * Test write with a boolean true.
     */
    public function testWriteWithTrue(): void
    {
        $this->expectOutputString('true');
        write(true);
    }

    /**
     * Test write with a boolean false.
     */
    public function testWriteWithFalse(): void
    {
        $this->expectOutputString('false');
        write(false);
    }

    /**
     * Test write with null.
     */
    public function testWriteWithNull(): void
    {
        $this->expectOutputString('null');
        write(null);
    }

    /**
     * Test write with an object that has a __toString() method.
     */
    public function testWriteWithStringableObject(): void
    {
        $obj = new class {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $this->expectOutputString('stringable object');
        write($obj);
    }

    #endregion

    #region writeln() tests

    /**
     * Test writeln outputs a string with a newline.
     */
    public function testWritelnWithString(): void
    {
        $this->expectOutputString('Hello' . PHP_EOL);
        writeln('Hello');
    }

    /**
     * Test writeln outputs an integer with a newline.
     */
    public function testWritelnWithInt(): void
    {
        $this->expectOutputString('42' . PHP_EOL);
        writeln(42);
    }

    /**
     * Test writeln outputs a float with a newline.
     */
    public function testWritelnWithFloat(): void
    {
        $this->expectOutputString('3.14' . PHP_EOL);
        writeln(3.14);
    }

    /**
     * Test writeln with no argument outputs just a newline.
     */
    public function testWritelnWithNoArgument(): void
    {
        $this->expectOutputString(PHP_EOL);
        writeln();
    }

    /**
     * Test writeln with an empty string outputs just a newline.
     */
    public function testWritelnWithEmptyString(): void
    {
        $this->expectOutputString(PHP_EOL);
        writeln('');
    }

    /**
     * Test writeln with a boolean true.
     */
    public function testWritelnWithTrue(): void
    {
        $this->expectOutputString('true' . PHP_EOL);
        writeln(true);
    }

    /**
     * Test writeln with a boolean false.
     */
    public function testWritelnWithFalse(): void
    {
        $this->expectOutputString('false' . PHP_EOL);
        writeln(false);
    }

    /**
     * Test writeln with null.
     */
    public function testWritelnWithNull(): void
    {
        $this->expectOutputString('null' . PHP_EOL);
        writeln(null);
    }

    /**
     * Test writeln with an object that has a __toString() method.
     */
    public function testWritelnWithStringableObject(): void
    {
        $obj = new class {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $this->expectOutputString('stringable object' . PHP_EOL);
        writeln($obj);
    }

    #endregion
}
