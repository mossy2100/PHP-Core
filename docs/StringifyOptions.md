# StringifyOptions

Read-only options for one call to [Stringify](Stringify.md): whether to pretty print, and the layout to use.

---

## Overview

`Stringify::stringify()`, `stringifyArray()` and `stringifyObject()` take an optional `StringifyOptions`. Without one,
output is compact, on a single line.

The options apply to that one call only. They're passed with the call rather than stored in `Stringify`, so two calls
with different options never affect each other.

The indent and max line length only matter when pretty printing is on, which is why all three settings live together.

```php
Stringify::stringify($value);                                                  // compact
Stringify::stringify($value, StringifyOptions::pretty());                      // pretty, default layout
Stringify::stringify($value, StringifyOptions::pretty(indent: 2, maxLineLength: 80));
```

---

## Properties

### prettyPrint

```php
public bool $prettyPrint
```

Whether arrays and objects are formatted over multiple lines, with indentation.

### indent

```php
public int $indent
```

The number of spaces to indent each level when pretty printing. Ignored for compact output.

### maxLineLength

```php
public int $maxLineLength
```

The maximum line length when pretty printing. It decides when a list of simple values wraps from a single line to one
value per line. Other long values, such as strings, aren't shortened to fit. Ignored for compact output.

---

## Constants

### DEFAULT_INDENT

```php
public const int DEFAULT_INDENT = 4;
```

The default number of spaces to indent each level.

### DEFAULT_MAX_LINE_LENGTH

```php
public const int DEFAULT_MAX_LINE_LENGTH = 120;
```

The default maximum line length for pretty-printed output.

---

## Constructor

```php
public function __construct(
    bool $prettyPrint = false,
    int $indent = self::DEFAULT_INDENT,
    int $maxLineLength = self::DEFAULT_MAX_LINE_LENGTH
)
```

Create a set of options. The factory methods below are usually more convenient.

**Parameters:**

- `$prettyPrint` (bool) - Whether to pretty print (default: `false`).
- `$indent` (int) - The number of spaces to indent each level. Must not be negative (default: `4`).
- `$maxLineLength` (int) - The maximum line length. Must be positive (default: `120`).

**Throws:**

- `DomainException` - If `$indent` is negative, or `$maxLineLength` is not positive.

**Examples:**

```php
new StringifyOptions();                        // compact
new StringifyOptions(true, maxLineLength: 80); // pretty, with shorter lines
new StringifyOptions(indent: -1);              // throws DomainException
```

---

## Factory Methods

### compact()

```php
public static function compact(): self
```

Get options for compact output, on a single line. This is what `null` options mean.

**Returns:** `self` - The options.

**Examples:**

```php
Stringify::stringify([1, [2, 3]], StringifyOptions::compact()); // '[1, [2, 3]]'
```

### pretty()

```php
public static function pretty(
    int $indent = self::DEFAULT_INDENT,
    int $maxLineLength = self::DEFAULT_MAX_LINE_LENGTH
): self
```

Get options for pretty-printed output, over multiple lines with indentation.

**Parameters:**

- `$indent` (int) - The number of spaces to indent each level. Must not be negative (default: `4`).
- `$maxLineLength` (int) - The maximum line length. Must be positive (default: `120`).

**Returns:** `self` - The options.

**Throws:**

- `DomainException` - If `$indent` is negative, or `$maxLineLength` is not positive.

**Examples:**

```php
Stringify::stringify(['a' => 1, 'b' => 2], StringifyOptions::pretty(indent: 2));
// [
//   'a' => 1,
//   'b' => 2,
// ]
```

---

## See Also

- **[Stringify](Stringify.md)** - The methods that take these options.
- **[Console](Console.md)** - `dump()` uses `StringifyOptions::pretty()`.
