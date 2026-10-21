<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Fixtures;

/**
 * Test fixture with properties of every visibility, for object-stringification tests.
 */
class Foo
{
    public int $a = 1;

    protected int $b = 2;

    private int $c = 3; // @phpstan-ignore property.onlyWritten
}
