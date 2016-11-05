<?php

namespace Runalyze\Metrics\HeartRate;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\HeartRate\Unit\BeatsPerMinute;

class HeartRate extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return BeatsPerMinute::class;
    }
}
