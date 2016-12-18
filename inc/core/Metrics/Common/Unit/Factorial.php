<?php

namespace Runalyze\Metrics\Common\Unit;

use Runalyze\Metrics\Common\FormattableUnitInterface;
use Runalyze\Metrics\Common\UnitConversionByFactorTrait;

class Factorial extends Simple implements FormattableUnitInterface
{
    use UnitConversionByFactorTrait;

    /** @var string */
    protected $Appendix;

    /** @var int|float */
    protected $Factor;

    /** @var int */
    protected $Decimals;

    /**
     * @param string $appendix
     * @param int|float $factor
     * @param int|null $decimals
     */
    public function __construct($appendix, $factor, $decimals = null)
    {
        parent::__construct($appendix);

        $this->Factor = $factor;
        $this->Decimals = $decimals ?: max(0, ceil(log10(1/$factor)));
    }

    /**
     * @return int|float
     */
    public function getFactorFromBaseUnit()
    {
        return $this->Factor;
    }

    /**
     * @return int
     */
    public function getDecimals()
    {
        return $this->Decimals;
    }
}
