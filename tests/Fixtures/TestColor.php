<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Fixtures;

/**
 * Test backed enum for getBasicType() tests.
 */
enum TestColor: string
{
    case Red = 'red';

    case Green = 'green';

    case Blue = 'blue';
}
