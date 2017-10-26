<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

class InvalidRRIntervalFilter extends AbstractFilter
{
    public function filter(ActivityDataContainer $container)
    {
        $container->RRIntervals = array_values(array_filter($container->RRIntervals));
    }
}
