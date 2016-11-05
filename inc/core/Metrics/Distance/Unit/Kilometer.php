<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;

class Kilometer extends AbstractDistanceUnit
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
}
