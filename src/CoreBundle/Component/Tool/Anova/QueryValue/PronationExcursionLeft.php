<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Anova\QueryValue;

use Runalyze\Bundle\CoreBundle\Component\Configuration\UnitSystem;
use Runalyze\Metrics\Common\Unit\Simple;

class PronationExcursionLeft extends AbstractOneColumnValue
{
    protected function getColumn()
    {
        return 'avgPronationExcursionLeft';
    }

    /**
     * @param UnitSystem $unitSystem
     * @return Simple
     */
    public function getValueUnit(UnitSystem $unitSystem)
    {
        return new Simple('°');
    }
}
