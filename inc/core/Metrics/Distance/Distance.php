<?php

namespace Runalyze\Metrics\Distance;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Distance\Unit\Kilometer;

class Distance extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return Kilometer::class;
    }
}
