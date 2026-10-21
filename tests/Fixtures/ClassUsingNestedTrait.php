<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Fixtures;

/**
 * Class that uses a trait which itself uses another trait.
 */
class ClassUsingNestedTrait
{
    use NestedTrait;
}
