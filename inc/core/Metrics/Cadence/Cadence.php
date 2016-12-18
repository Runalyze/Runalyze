<?php

namespace Runalyze\Metrics\Cadence;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Cadence\Unit\RoundsPerMinute;

class Cadence extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return RoundsPerMinute::class;
    }
}
