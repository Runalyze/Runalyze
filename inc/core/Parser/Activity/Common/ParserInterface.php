<?php

namespace Runalyze\Parser\Activity\Common;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

interface ParserInterface
{
    public function parse();

    /**
     * @return int
     */
    public function getNumberOfActivities();

    /**
     * @param int $index
     * @return ActivityDataContainer
     */
    public function getActivityDataContainer($index = 0);
}
