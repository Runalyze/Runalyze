<?php

namespace Runalyze\Metrics\Distance;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Distance\Unit\Meter;

class Elevation extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return Meter::class;
    }
}
