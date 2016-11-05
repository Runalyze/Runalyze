<?php

namespace Runalyze\Metrics\Energy\Unit;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class EnergyEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const KILOCALORIES = 0;

    /** @var int */
    const KILOJOULES = 1;
}
