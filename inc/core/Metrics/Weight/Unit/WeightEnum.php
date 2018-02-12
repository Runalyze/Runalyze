<?php

namespace Runalyze\Metrics\Weight\Unit;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;

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
