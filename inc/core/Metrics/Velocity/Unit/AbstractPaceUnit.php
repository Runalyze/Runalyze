<?php

namespace Runalyze\Metrics\Velocity\Unit;

use Runalyze\Metrics\Common\AbstractComparableUnit;

abstract class AbstractPaceUnit extends AbstractComparableUnit
{
    /**
     * @return string
     */
    public function getUnit()
    {
        $appendix = $this->getAppendix();

        if ('/' == substr($appendix, 0, 1)) {
            $appendix = 'min'.$appendix;
        }

        return $appendix;
    }
}
