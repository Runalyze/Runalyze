<?php

namespace Runalyze\Metrics\Energy;

use Runalyze\Metrics\Common\AbstractMetric;
use Runalyze\Metrics\Energy\Unit\Kilocalories;

class Energy extends AbstractMetric
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getBaseUnitClass()
    {
        return Kilocalories::class;
    }
}
