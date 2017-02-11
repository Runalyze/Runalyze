<?php

namespace Runalyze\Metrics\Velocity;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Velocity\Unit\SecondsPerKilometer;

class Pace extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return SecondsPerKilometer::class;
    }
}
