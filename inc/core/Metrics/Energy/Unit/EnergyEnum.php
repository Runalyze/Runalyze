<?php

namespace Runalyze\Metrics\Energy\Unit;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;

class EnergyEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const KILOCALORIES = 0;

    /** @var int */
    const KILOJOULES = 1;
}
