<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Metrics\Energy\Unit\AbstractEnergyUnit;

class EnergyType extends AbstractUnitBasedType
{
    /** @var int */
    protected $ViewPrecision = 0;

    public function __construct(AbstractEnergyUnit $energyUnit)
    {
        parent::__construct($energyUnit);
    }
}
