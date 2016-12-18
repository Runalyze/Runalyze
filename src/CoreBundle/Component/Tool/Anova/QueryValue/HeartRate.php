<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\HeartRate\Unit\AbstractHeartRateUnit;

class HeartRate extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 'pulseAvg';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractHeartRateUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return $unitSystem->getHeartRateUnit();
    }
}
