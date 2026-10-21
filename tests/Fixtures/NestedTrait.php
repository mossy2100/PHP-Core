<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Fixtures;

/**
 * Nested trait that uses another trait.
 */
trait NestedTrait
{
    use TestTrait;
}
