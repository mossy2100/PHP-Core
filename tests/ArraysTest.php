<?php

declare(strict_types = 1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Arrays;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test class for Arrays utility class.
 */
#[CoversClass(Arrays::class)]
final class ArraysTest extends TestCase
{
    /**
     * Test that simple arrays without recursion return false.
     */
    public function testContainsRecursionSimpleArray(): void
    {
        // Test empty array.
        $this->assertFalse(Arrays::containsRecursion([]));

        // Test simple flat array.
        $this->assertFalse(Arrays::containsRecursion([1, 2, 3]));

        // Test associative array.
        $this->assertFalse(Arrays::containsRecursion(['name' => 'John', 'age' => 30]));

        // Test array with mixed types.
        $this->assertFalse(Arrays::containsRecursion([1, 'hello', true, null, 3.14]));
    }

    /**
     * Test that nested arrays without recursion return false.
     */
    public function testContainsRecursionNestedArray(): void
    {
        // Test nested array without recursion.
        $this->assertFalse(Arrays::containsRecursion([[1, 2], [3, 4]]));

        // Test deeply nested array without recursion.
        $this->assertFalse(Arrays::containsRecursion([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 42
                    ]
                ]
            ]
        ]));

        // Test array containing objects without recursion.
        $obj = new stdClass();
        $obj->name = 'test';
        $this->assertFalse(Arrays::containsRecursion(['object' => $obj]));
    }

    /**
     * Test that arrays with direct self-reference return true.
     */
    public function testContainsRecursionDirectReference(): void
    {
        // Create array with direct self-reference.
        $arr = ['foo' => 'bar'];
        $arr['self'] = &$arr;

        // Test that recursion is detected.
        $this->assertTrue(Arrays::containsRecursion($arr));
    }

    /**
     * Test that arrays with indirect recursion return true.
     */
    public function testContainsRecursionIndirectReference(): void
    {
        // Create array with indirect recursion.
        $arr1 = ['name' => 'array1'];
        $arr2 = ['name' => 'array2'];
        $arr1['child'] = &$arr2;
        $arr2['parent'] = &$arr1;

        // Test that recursion is detected in first array.
        $this->assertTrue(Arrays::containsRecursion($arr1));

        // Test that recursion is detected in second array.
        $this->assertTrue(Arrays::containsRecursion($arr2));
    }

    /**
     * Test that arrays with nested recursion return true.
     */
    public function testContainsRecursionNestedReference(): void
    {
        // Create array with recursion at a nested level.
        $arr = [
            'level1' => [
                'level2' => [
                    'level3' => []
                ]
            ]
        ];
        $arr['level1']['level2']['level3']['back'] = &$arr;

        // Test that nested recursion is detected.
        $this->assertTrue(Arrays::containsRecursion($arr));
    }

    /**
     * Test that arrays with self-reference at different positions return true.
     */
    public function testContainsRecursionMultipleReferences(): void
    {
        // Create array with multiple references to itself.
        $arr = ['a' => 1, 'b' => 2];
        $arr['ref1'] = &$arr;
        $arr['ref2'] = &$arr;

        // Test that recursion is detected even with multiple references.
        $this->assertTrue(Arrays::containsRecursion($arr));
    }

    /**
     * Test that arrays containing references to sub-arrays don't cause false positives.
     */
    public function testContainsRecursionSubArrayReference(): void
    {
        // Create array with reference to a sub-array (not recursion).
        $subArray = ['x' => 1, 'y' => 2];
        $arr = [
            'original' => $subArray,
            'reference' => &$subArray
        ];

        // Test that this is not detected as recursion (it's just a reference to a sub-array).
        $result = Arrays::containsRecursion($arr);

        $this->assertFalse($result);
    }

    /**
     * Test with array containing various data types and no recursion.
     */
    public function testContainsRecursionComplexNonRecursive(): void
    {
        // Create complex array without recursion.
        $arr = [
            'null' => null,
            'bool' => true,
            'int' => 42,
            'float' => 3.14,
            'string' => 'hello',
            'array' => [1, 2, 3],
            'nested' => [
                'deep' => [
                    'value' => 'test'
                ]
            ],
            'object' => new stdClass()
        ];

        // Test that no recursion is detected.
        $this->assertFalse(Arrays::containsRecursion($arr));
    }
}
