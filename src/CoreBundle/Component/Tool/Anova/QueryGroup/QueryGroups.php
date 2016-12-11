<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryGroup;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

final class QueryGroups extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var string */
    //const WEEK = 'week';

    /** @var string */
    //const MONTH = 'month';

    /** @var int */
    //const YEAR = 'year';

    /** @var int */
    const SPORT = 'sport';

    /** @var int */
    const TYPE = 'type';

    /** @var int */
    //const EQUIPMENT = 'equipment';
}
