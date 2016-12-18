<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Power\Unit\Watts;

class Power extends AbstractOneColumnValue
{
    /**
     * @return string
     */
    protected function getColumn()
    {
        return 'power';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return Watts
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Watts();
    }
}
