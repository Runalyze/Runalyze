<?php

namespace Runalyze\Metrics\Common\Unit;

use Runalyze\Metrics\Common\FormattableUnitInterface;
use Runalyze\Metrics\Common\UnitConversionByFactorTrait;
use Runalyze\Metrics\Common\UnitInterface;

class Linear implements UnitInterface, FormattableUnitInterface
{
    /** @var string */
    protected $Appendix;

    /** @var int */
    protected $Decimals;

    /** @var callable */
    protected $TransformationFromBaseUnit;

    /** @var callable */
    protected $TransformationToBaseUnit;

    /**
     * @param callable $transformationFromBaseUnit
     * @param callable $transformationToBaseUnit
     * @param string $appendix
     * @param int $decimals
     */
    public function __construct(callable $transformationFromBaseUnit, callable $transformationToBaseUnit, $appendix = '', $decimals = 0)
    {
        $this->TransformationFromBaseUnit = $transformationFromBaseUnit;
        $this->TransformationToBaseUnit = $transformationToBaseUnit;
        $this->Appendix = $appendix;
        $this->Decimals = $decimals;
    }

    public function getAppendix()
    {
        return $this->Appendix;
    }

    public function toBaseUnit($valueInThisUnit)
    {
        return call_user_func($this->TransformationToBaseUnit, $valueInThisUnit);
    }

    public function fromBaseUnit($valueInBaseUnit)
    {
        return call_user_func($this->TransformationFromBaseUnit, $valueInBaseUnit);
    }

    public function getDecimals()
    {
        return $this->Decimals;
    }
}
