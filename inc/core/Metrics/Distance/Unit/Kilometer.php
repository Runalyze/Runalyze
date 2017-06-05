<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;
use Runalyze\Metrics\Common\FormattableUnitInterface;

class Kilometer extends AbstractDistanceUnit implements FormattableUnitInterface
{
    use BaseUnitTrait;

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return 'km';
    }

    public function getDecimals()
    {
        return 2;
    }
}
