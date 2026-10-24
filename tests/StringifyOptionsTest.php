<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests;

use DomainException;
use Mossy2100\Core\Stringify;
use Mossy2100\Core\StringifyOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for StringifyOptions.
 */
#[CoversClass(StringifyOptions::class)]
final class StringifyOptionsTest extends TestCase
{
    #region Method __construct() tests.

    /**
     * Test the constructor's defaults give compact output with the default layout.
     */
    public function testConstructorDefaults(): void
    {
        $options = new StringifyOptions();

        $this->assertFalse($options->prettyPrint);
        $this->assertSame(StringifyOptions::DEFAULT_INDENT, $options->indent);
        $this->assertSame(StringifyOptions::DEFAULT_MAX_LINE_LENGTH, $options->maxLineLength);
    }

    /**
     * Test the constructor accepts the smallest valid indent and max line length.
     */
    public function testConstructorAcceptsLimits(): void
    {
        $options = new StringifyOptions(true, 0, 1);

        $this->assertTrue($options->prettyPrint);
        $this->assertSame(0, $options->indent);
        $this->assertSame(1, $options->maxLineLength);
    }

    /**
     * Test the constructor throws for a negative indent.
     */
    public function testConstructorNegativeIndentThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid indent: -1. Must not be negative.');
        new StringifyOptions(indent: -1);
    }

    /**
     * Test the constructor throws for a max line length that isn't positive.
     */
    public function testConstructorNonPositiveMaxLineLengthThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid max line length: 0. Must be positive.');
        new StringifyOptions(maxLineLength: 0);
    }

    #endregion

    #region Method compact() tests.

    /**
     * Test compact() gives options with pretty printing off.
     */
    public function testCompact(): void
    {
        $value = [
            1,
            [2, 3],
        ];

        $this->assertFalse(StringifyOptions::compact()->prettyPrint);
        $this->assertSame('[1, [2, 3]]', Stringify::stringify($value, StringifyOptions::compact()));
    }

    #endregion

    #region Method pretty() tests.

    /**
     * Test pretty() gives options with pretty printing on and the default layout.
     */
    public function testPrettyDefaults(): void
    {
        $options = StringifyOptions::pretty();

        $this->assertTrue($options->prettyPrint);
        $this->assertSame(StringifyOptions::DEFAULT_INDENT, $options->indent);
        $this->assertSame(StringifyOptions::DEFAULT_MAX_LINE_LENGTH, $options->maxLineLength);
    }

    /**
     * Test pretty() passes a custom layout through to the output.
     */
    public function testPrettyCustomLayout(): void
    {
        $value = [
            'a' => [1, 2],
        ];

        $this->assertSame("[\n  'a' => [1, 2],\n]", Stringify::stringify($value, StringifyOptions::pretty(indent: 2)));
        $this->assertSame("[\n    'a' => [1, 2],\n]", Stringify::stringify($value, StringifyOptions::pretty()));
    }

    /**
     * Test pretty() validates its arguments.
     */
    public function testPrettyNegativeIndentThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid indent: -2. Must not be negative.');
        StringifyOptions::pretty(indent: -2);
    }

    #endregion
}
