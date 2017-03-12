<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Temperature\Unit\AbstractTemperatureUnit;

class TemperatureType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 1;

    public function __construct(AbstractTemperatureUnit $temperatureUnit)
    {
        parent::__construct($temperatureUnit);
    }
}
