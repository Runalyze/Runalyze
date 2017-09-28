<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

interface FilterInterface
{
    /**
     * @param ActivityDataContainer $container
     */
    public function filter(ActivityDataContainer $container);
}
