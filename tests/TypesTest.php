<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use DateTime;
use Galaxon\Core\Types;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

/**
 * Test class for Type utility class.
 */
#[CoversClass(Types::class)]
final class TypesTest extends TestCase
{
    /**
     * Test detection of numeric types.
     */
    public function testIsNumber(): void
    {
        // Test that integers are identified as numbers.
        $this->assertTrue(Types::isNumber(0));
        $this->assertTrue(Types::isNumber(42));
        $this->assertTrue(Types::isNumber(-17));

        // Test that floats are identified as numbers.
        $this->assertTrue(Types::isNumber(0.0));
        $this->assertTrue(Types::isNumber(3.14));
        $this->assertTrue(Types::isNumber(-2.5));

        // Test that special float values are identified as numbers.
        $this->assertTrue(Types::isNumber(INF));
        $this->assertTrue(Types::isNumber(-INF));
        $this->assertTrue(Types::isNumber(NAN));

        // Test that numeric strings are NOT identified as numbers.
        $this->assertFalse(Types::isNumber("42"));
        $this->assertFalse(Types::isNumber("3.14"));

        // Test that other types are not identified as numbers.
        $this->assertFalse(Types::isNumber("hello"));
        $this->assertFalse(Types::isNumber(true));
        $this->assertFalse(Types::isNumber(false));
        $this->assertFalse(Types::isNumber(null));
        $this->assertFalse(Types::isNumber([]));
        $this->assertFalse(Types::isNumber(new stdClass()));
    }

    /**
     * Test detection of unsigned integers.
     */
    public function testIsUint(): void
    {
        // Test that zero is identified as unsigned integer.
        $this->assertTrue(Types::isUint(0));

        // Test that positive integers are identified as unsigned integers.
        $this->assertTrue(Types::isUint(1));
        $this->assertTrue(Types::isUint(42));
        $this->assertTrue(Types::isUint(1000000));

        // Test that negative integers are NOT identified as unsigned integers.
        $this->assertFalse(Types::isUint(-1));
        $this->assertFalse(Types::isUint(-42));

        // Test that floats are NOT identified as unsigned integers.
        $this->assertFalse(Types::isUint(0.0));
        $this->assertFalse(Types::isUint(3.14));
        $this->assertFalse(Types::isUint(-2.5));

        // Test that other types are not identified as unsigned integers.
        $this->assertFalse(Types::isUint("42"));
        $this->assertFalse(Types::isUint(true));
        $this->assertFalse(Types::isUint(null));
    }

    /**
     * Test getBasicType with null.
     */
    public function testGetBasicTypeNull(): void
    {
        // Test that null returns 'null'.
        $this->assertSame('null', Types::getBasicType(null));
    }

    /**
     * Test getBasicType with boolean values.
     */
    public function testGetBasicTypeBool(): void
    {
        // Test that booleans return 'bool'.
        $this->assertSame('bool', Types::getBasicType(true));
        $this->assertSame('bool', Types::getBasicType(false));
    }

    /**
     * Test getBasicType with integers.
     */
    public function testGetBasicTypeInt(): void
    {
        // Test that integers return 'int'.
        $this->assertSame('int', Types::getBasicType(0));
        $this->assertSame('int', Types::getBasicType(42));
        $this->assertSame('int', Types::getBasicType(-17));
    }

    /**
     * Test getBasicType with floats.
     */
    public function testGetBasicTypeFloat(): void
    {
        // Test that floats return 'float'.
        $this->assertSame('float', Types::getBasicType(0.0));
        $this->assertSame('float', Types::getBasicType(3.14));
        $this->assertSame('float', Types::getBasicType(-2.5));

        // Test special float values.
        $this->assertSame('float', Types::getBasicType(INF));
        $this->assertSame('float', Types::getBasicType(-INF));
        $this->assertSame('float', Types::getBasicType(NAN));
    }

    /**
     * Test getBasicType with strings.
     */
    public function testGetBasicTypeString(): void
    {
        // Test that strings return 'string'.
        $this->assertSame('string', Types::getBasicType(''));
        $this->assertSame('string', Types::getBasicType('hello'));
        $this->assertSame('string', Types::getBasicType('42'));
    }

    /**
     * Test getBasicType with arrays.
     */
    public function testGetBasicTypeArray(): void
    {
        // Test that arrays return 'array'.
        $this->assertSame('array', Types::getBasicType([]));
        $this->assertSame('array', Types::getBasicType([1, 2, 3]));
        $this->assertSame('array', Types::getBasicType(['key' => 'value']));
    }

    /**
     * Test getBasicType with objects.
     */
    public function testGetBasicTypeObject(): void
    {
        // Test that objects return 'object'.
        $this->assertSame('object', Types::getBasicType(new stdClass()));
        $this->assertSame('object', Types::getBasicType(new DateTime()));
        $this->assertSame('object', Types::getBasicType(new class {
        }));
    }

    /**
     * Test getBasicType with resources.
     */
    public function testGetBasicTypeResource(): void
    {
        // Test that resources return 'resource'.
        $resource = fopen('php://memory', 'rb');
        $this->assertSame('resource', Types::getBasicType($resource));
        fclose($resource);
    }

    /**
     * Test getStringKey with null.
     */
    public function testGetStringKeyNull(): void
    {
        // Test that null produces unique key.
        $this->assertSame('n', Types::getUniqueString(null));
    }

    /**
     * Test getStringKey with boolean values.
     */
    public function testGetStringKeyBool(): void
    {
        // Test that booleans produce unique keys.
        $this->assertSame('b:T', Types::getUniqueString(true));
        $this->assertSame('b:F', Types::getUniqueString(false));

        // Test that true and false produce different keys.
        $this->assertNotSame(Types::getUniqueString(true), Types::getUniqueString(false));
    }

    /**
     * Test getStringKey with integers.
     */
    public function testGetStringKeyInt(): void
    {
        // Test that integers produce unique keys.
        $this->assertSame('i:0', Types::getUniqueString(0));
        $this->assertSame('i:42', Types::getUniqueString(42));
        $this->assertSame('i:-17', Types::getUniqueString(-17));

        // Test that different integers produce different keys.
        $this->assertNotSame(Types::getUniqueString(1), Types::getUniqueString(2));
    }

    /**
     * Test getStringKey with floats.
     */
    public function testGetStringKeyFloat(): void
    {
        // Test that floats produce unique keys starting with 'f:'.
        $key1 = Types::getUniqueString(3.14);
        $this->assertStringStartsWith('f:', $key1);

        // Test that different floats produce different keys.
        $key2 = Types::getUniqueString(2.71);
        $this->assertNotSame($key1, $key2);

        // Test that positive and negative zero produce different keys.
        $keyPosZero = Types::getUniqueString(0.0);
        $keyNegZero = Types::getUniqueString(-0.0);
        $this->assertNotSame($keyPosZero, $keyNegZero);

        // Test special float values produce unique keys.
        $this->assertStringStartsWith('f:', Types::getUniqueString(INF));
        $this->assertStringStartsWith('f:', Types::getUniqueString(-INF));
        $this->assertStringStartsWith('f:', Types::getUniqueString(NAN));
    }

    /**
     * Test getStringKey with strings.
     */
    public function testGetStringKeyString(): void
    {
        // Test that strings produce keys with format 's:length:content'.
        $this->assertSame('s:5:hello', Types::getUniqueString('hello'));
        $this->assertSame('s:0:', Types::getUniqueString(''));
        $this->assertSame('s:2:42', Types::getUniqueString('42'));

        // Test that different strings produce different keys.
        $this->assertNotSame(Types::getUniqueString('hello'), Types::getUniqueString('world'));

        // Test that strings with same length but different content produce different keys.
        $this->assertNotSame(Types::getUniqueString('abc'), Types::getUniqueString('def'));
    }

    /**
     * Test getStringKey with arrays.
     */
    public function testGetStringKeyArray(): void
    {
        // Test that arrays produce keys starting with 'a:count:'.
        $key1 = Types::getUniqueString([1, 2, 3]);
        $this->assertStringStartsWith('a:3:', $key1);

        // Test empty array.
        $key2 = Types::getUniqueString([]);
        $this->assertStringStartsWith('a:0:', $key2);

        // Test that different arrays produce different keys.
        $this->assertNotSame(Types::getUniqueString([1, 2]), Types::getUniqueString([3, 4]));

        // Test associative array.
        $key3 = Types::getUniqueString(['key' => 'value']);
        $this->assertStringStartsWith('a:1:', $key3);
    }

    /**
     * Test getStringKey with objects.
     */
    public function testGetStringKeyObject(): void
    {
        // Test that objects produce keys with their object ID.
        $obj1 = new stdClass();
        $key1 = Types::getUniqueString($obj1);
        $this->assertStringStartsWith('o:', $key1);

        // Test that different objects produce different keys.
        $obj2 = new stdClass();
        $key2 = Types::getUniqueString($obj2);
        $this->assertNotSame($key1, $key2);

        // Test that same object produces same key.
        $key3 = Types::getUniqueString($obj1);
        $this->assertSame($key1, $key3);
    }

    /**
     * Test getStringKey with resources.
     */
    public function testGetStringKeyResource(): void
    {
        // Test that resources produce keys with their resource ID.
        $resource = fopen('php://memory', 'rb');
        $key = Types::getUniqueString($resource);
        $this->assertStringStartsWith('r:', $key);
        fclose($resource);

        // Test that different resources produce different keys.
        $resource1 = fopen('php://memory', 'rb');
        $resource2 = fopen('php://memory', 'rb');
        $this->assertNotSame(Types::getUniqueString($resource1), Types::getUniqueString($resource2));
        fclose($resource1);
        fclose($resource2);
    }

    /**
     * Test getStringKey produces unique keys for different types.
     */
    public function testGetStringKeyUniqueness(): void
    {
        // Test that different types produce different keys.
        $keys = [
            Types::getUniqueString(null),
            Types::getUniqueString(true),
            Types::getUniqueString(0),
            Types::getUniqueString(0.0),
            Types::getUniqueString(''),
            Types::getUniqueString([]),
            Types::getUniqueString(new stdClass()),
        ];

        // Verify all keys are unique.
        $this->assertCount(count($keys), array_unique($keys));
    }

    /**
     * Test createError with variable name and expected type only.
     */
    public function testCreateErrorBasic(): void
    {
        // Test error message without value.
        $error = Types::createError('myVar', 'int');
        $this->assertInstanceOf(TypeError::class, $error);
        $this->assertSame("Variable 'myVar' must be of type int.", $error->getMessage());
    }

    /**
     * Test createError with variable name, expected type, and actual value.
     */
    public function testCreateErrorWithValue(): void
    {
        // Test error message with value.
        $error = Types::createError('myVar', 'int', 'hello');
        $this->assertInstanceOf(TypeError::class, $error);
        $this->assertSame("Variable 'myVar' must be of type int, string given.", $error->getMessage());
    }

    /**
     * Test createError with different types.
     */
    public function testCreateErrorDifferentTypes(): void
    {
        // Test with array given.
        $error = Types::createError('items', 'string', [1, 2, 3]);
        $this->assertStringContainsString('array given', $error->getMessage());

        // Test with object given.
        $error = Types::createError('obj', 'callable', new stdClass());
        $this->assertStringContainsString('stdClass given', $error->getMessage());

        // Test with null given.
        $error = Types::createError('value', 'string', null);
        $this->assertStringContainsString('null given', $error->getMessage());
    }

    /**
     * Test usesTrait with object using a trait.
     */
    public function testUsesTraitWithObject(): void
    {
        // Create a test trait and class.
        $obj = new class {
            use TestTrait;
        };

        // Test that the method correctly detects trait usage.
        $this->assertTrue(Types::usesTrait($obj, TestTrait::class));

        // Test that it returns false for non-existent trait.
        $this->assertFalse(Types::usesTrait($obj, 'NonExistentTrait'));
    }

    /**
     * Test usesTrait with class name string.
     */
    public function testUsesTraitWithClassName(): void
    {
        // Test with class name string.
        $this->assertTrue(Types::usesTrait(ClassUsingTrait::class, TestTrait::class));
        $this->assertFalse(Types::usesTrait(ClassNotUsingTrait::class, TestTrait::class));
    }

    /**
     * Test usesTrait with inherited traits.
     */
    public function testUsesTraitWithInheritance(): void
    {
        // Test that trait usage is detected through parent class.
        $obj = new ChildClassUsingTrait();
        $this->assertTrue(Types::usesTrait($obj, TestTrait::class));
    }

    /**
     * Test usesTrait with traits that use other traits.
     */
    public function testUsesTraitWithNestedTraits(): void
    {
        // Test that nested trait usage is detected.
        $obj = new ClassUsingNestedTrait();
        $this->assertTrue(Types::usesTrait($obj, TestTrait::class));
        $this->assertTrue(Types::usesTrait($obj, NestedTrait::class));
    }

    /**
     * Test usesTrait with object not using any traits.
     */
    public function testUsesTraitWithNoTraits(): void
    {
        // Test with standard class that doesn't use traits.
        $obj = new stdClass();
        $this->assertFalse(Types::usesTrait($obj, TestTrait::class));
    }
}

// Test fixtures for trait testing.

/**
 * Test trait for trait detection tests.
 */
trait TestTrait
{
    public function testMethod(): string
    {
        return 'test';
    }
}

/**
 * Nested trait that uses another trait.
 */
trait NestedTrait
{
    use TestTrait;
}

/**
 * Class that uses a trait.
 */
class ClassUsingTrait
{
    use TestTrait;
}

/**
 * Class that doesn't use any traits.
 */
class ClassNotUsingTrait
{
}

/**
 * Child class that inherits trait usage from parent.
 */
class ChildClassUsingTrait extends ClassUsingTrait
{
}

/**
 * Class that uses a trait which itself uses another trait.
 */
class ClassUsingNestedTrait
{
    use NestedTrait;
}
