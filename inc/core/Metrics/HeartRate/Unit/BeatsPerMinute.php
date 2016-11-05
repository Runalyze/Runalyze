<?php

namespace Runalyze\Metrics\HeartRate\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;

class BeatsPerMinute extends AbstractHeartRateUnit
{
    use BaseUnitTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'bpm';
    }
}
