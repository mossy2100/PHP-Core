<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Stringify;

use DomainException;
use OceanMoon\Core\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Stringify utility class - configuration methods.
 */
#[CoversClass(Stringify::class)]
final class StringifyConfigurationTest extends TestCase
{
    #region Method getIndent() tests.

    /**
     * Test getIndent() returns the default value initially.
     */
    public function testGetIndentDefault(): void
    {
        $this->assertSame(Stringify::DEFAULT_INDENT, Stringify::getIndent());
    }

    #endregion

    #region Method setIndent() tests.

    /**
     * Test setIndent() changes the indent value.
     */
    public function testSetIndent(): void
    {
        Stringify::setIndent(2);
        try {
            $this->assertSame(2, Stringify::getIndent());

            // Verify it affects pretty-printed output.
            $result = Stringify::stringify([
                'a' => 1,
                'b' => 2,
            ], true);
            $expected = "[\n  'a' => 1,\n  'b' => 2,\n]";
            $this->assertSame($expected, $result);
        } finally {
            Stringify::resetDefaults();
        }
    }

    /**
     * Test setIndent() throws for negative value.
     */
    public function testSetIndentNegativeThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid indent:');
        Stringify::setIndent(-1);
    }

    #endregion

    #region Method getMaxLineLength() tests.

    /**
     * Test getMaxLineLength() returns the default value initially.
     */
    public function testGetMaxLineLengthDefault(): void
    {
        $this->assertSame(Stringify::DEFAULT_MAX_LINE_LENGTH, Stringify::getMaxLineLength());
    }

    #endregion

    #region Method setMaxLineLength() tests.

    /**
     * Test setMaxLineLength() changes the max line length.
     */
    public function testSetMaxLineLength(): void
    {
        Stringify::setMaxLineLength(60);
        try {
            $this->assertSame(60, Stringify::getMaxLineLength());
        } finally {
            Stringify::resetDefaults();
        }
    }

    /**
     * Test setMaxLineLength() throws for zero.
     */
    public function testSetMaxLineLengthZeroThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid max line length:');
        Stringify::setMaxLineLength(0);
    }

    /**
     * Test setMaxLineLength() throws for negative value.
     */
    public function testSetMaxLineLengthNegativeThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid max line length:');
        Stringify::setMaxLineLength(-10);
    }

    #endregion

    #region Method resetDefaults() tests.

    /**
     * Test resetDefaults() restores both values.
     */
    public function testResetDefaults(): void
    {
        Stringify::setIndent(8);
        Stringify::setMaxLineLength(40);
        Stringify::resetDefaults();

        $this->assertSame(Stringify::DEFAULT_INDENT, Stringify::getIndent());
        $this->assertSame(Stringify::DEFAULT_MAX_LINE_LENGTH, Stringify::getMaxLineLength());
    }

    #endregion
}
