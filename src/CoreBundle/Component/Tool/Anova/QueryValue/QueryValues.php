<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

final class QueryValues extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var string */
    const PACE = 'pace';

    /** @var string */
    const DISTANCE = 'distance';

    /** @var string */
    const DURATION = 'duration';

    /** @var string */
    const HEART_RATE = 'heart_rate';

    /** @var string */
    const TRIMP = 'trimp';

    /** @var string */
    const POWER = 'power';

    /** @var string */
    const CADENCE = 'cadence';

    /** @var string */
    const GROUND_CONTACT_TIME = 'ground_contact_time';

    /** @var string */
    const GROUND_CONTACT_BALANCE = 'ground_contact_balance';

    /** @var string */
    const VERTICAL_OSCILLATION = 'vertical_oscillation';
}
