<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Exceptions;

use DateTime;
use InvalidArgumentException;
use OceanMoon\Core\Exceptions\IncomparableTypesException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test class for IncomparableTypesException.
 */
#[CoversClass(IncomparableTypesException::class)]
final class IncomparableTypesExceptionTest extends TestCase
{
    /**
     * Test exception extends InvalidArgumentException.
     */
    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new IncomparableTypesException('hello', 42);
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    /**
     * Test message with scalar types.
     */
    public function testMessageWithScalarTypes(): void
    {
        $exception = new IncomparableTypesException('hello', 42);
        $this->assertSame("Can't compare string with int.", $exception->getMessage());
    }

    /**
     * Test message with float and bool.
     */
    public function testMessageWithFloatAndBool(): void
    {
        $exception = new IncomparableTypesException(3.14, true);
        $this->assertSame("Can't compare float with bool.", $exception->getMessage());
    }

    /**
     * Test message with null.
     */
    public function testMessageWithNull(): void
    {
        $exception = new IncomparableTypesException(null, 'test');
        $this->assertSame("Can't compare null with string.", $exception->getMessage());
    }

    /**
     * Test message with array.
     */
    public function testMessageWithArray(): void
    {
        $exception = new IncomparableTypesException([1, 2, 3], 'test');
        $this->assertSame("Can't compare array with string.", $exception->getMessage());
    }

    /**
     * Test message with stdClass object.
     */
    public function testMessageWithStdClass(): void
    {
        $exception = new IncomparableTypesException(new stdClass(), 42);
        $this->assertSame("Can't compare stdClass with int.", $exception->getMessage());
    }

    /**
     * Test message with named class.
     */
    public function testMessageWithNamedClass(): void
    {
        $exception = new IncomparableTypesException(new DateTime(), 'test');
        $this->assertSame("Can't compare DateTime with string.", $exception->getMessage());
    }

    /**
     * Test message with two different objects.
     */
    public function testMessageWithTwoDifferentObjects(): void
    {
        $exception = new IncomparableTypesException(new DateTime(), new stdClass());
        $this->assertSame("Can't compare DateTime with stdClass.", $exception->getMessage());
    }

    /**
     * Test message with same types (edge case - might happen with subclasses).
     */
    public function testMessageWithSameTypes(): void
    {
        $exception = new IncomparableTypesException(new DateTime(), new DateTime());
        $this->assertSame("Can't compare DateTime with DateTime.", $exception->getMessage());
    }

    /**
     * Test exception can be thrown and caught.
     */
    public function testCanBeThrown(): void
    {
        $this->expectException(IncomparableTypesException::class);
        $this->expectExceptionMessage("Can't compare string with int.");

        throw new IncomparableTypesException('hello', 42);
    }
}
