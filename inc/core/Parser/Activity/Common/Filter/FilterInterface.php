<?php

namespace Runalyze\Parser\Activity\Common\Filter;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

interface FilterInterface
{
    /**
     * @param ActivityDataContainer $container
     * @param bool $strict if disabled the filter tries to fix all issues, otherwise throw exceptions
     */
    public function filter(ActivityDataContainer $container, $strict = false);
}
