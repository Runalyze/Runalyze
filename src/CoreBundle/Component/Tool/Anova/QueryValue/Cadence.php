<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Cadence\Unit\AbstractCadenceUnit;

class Cadence extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 'cadence';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractCadenceUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return $unitSystem->getCadenceUnit();
    }
}
