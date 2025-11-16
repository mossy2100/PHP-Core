# Arrays

Container for useful array-related methods.

## Methods

### containsRecursion()

```php
public static function containsRecursion(array $arr): bool
```

Checks if an array contains circular references (recursion). This occurs when an array contains a reference to itself, either directly or indirectly through nested arrays.

**Parameters:**
- `$arr` (array) - The array to check for circular references

**Returns:**
- `bool` - Returns `true` if recursion is detected, `false` otherwise

**Examples:**

Direct recursion:
```php
$arr = ['foo' => 'bar'];
$arr['self'] = &$arr;
Arrays::containsRecursion($arr); // true
```

Indirect recursion:
```php
$arr1 = ['name' => 'array1'];
$arr2 = ['name' => 'array2'];
$arr1['child'] = &$arr2;
$arr2['parent'] = &$arr1;
Arrays::containsRecursion($arr1); // true
```

No recursion:
```php
$arr = [[1, 2], [3, 4]];
Arrays::containsRecursion($arr); // false
```

**Note:** This method uses `json_encode()` internally to detect recursion, as circular references cannot be JSON-encoded.
