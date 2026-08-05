<?php

declare(strict_types=1);

namespace OceanMoon\Core\Enums;

/**
 * Notation styles for Floats::format().
 */
enum FloatFormat
{
    /**
     * Use a decimal point, with no exponent.
     */
    case FixedPoint;

    /**
     * Include an exponent.
     */
    case Scientific;

    /**
     * Use whichever of FixedPoint or Scientific produces the more compact string.
     */
    case Auto;
}
