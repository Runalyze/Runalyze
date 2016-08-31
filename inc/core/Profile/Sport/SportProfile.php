<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class SportProfile extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const GENERIC = 0;

    /** @var int */
    const RUNNING = 1;

    /** @var int */
    const CYCLING = 2;

    /** @var int */
    const SWIMMING = 3;

    /** @var int */
    const ROWING = 4;

    /** @var int */
    const HIKING = 5;
}
