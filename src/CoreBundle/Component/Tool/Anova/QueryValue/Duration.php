<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Time\Unit\AbstractTimeUnit;
use Runalyze\Metrics\Time\Unit\Hours;

class Duration extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 's';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return AbstractTimeUnit
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Hours();
    }
}
