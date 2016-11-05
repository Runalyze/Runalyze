<?php

namespace Runalyze\Metrics\Weight\Unit;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class WeightEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const KILOGRAM = 0;

    /** @var int */
    const POUNDS = 1;

    /** @var int */
    const STONES = 2;
}
