<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Distance\Unit\AbstractDistanceUnit;

class Distance extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 'distance';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractDistanceUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return $unitSystem->getDistanceUnit();
    }
}
