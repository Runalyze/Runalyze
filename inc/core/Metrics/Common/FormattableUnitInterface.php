<?php

namespace Runalyze\Metrics\Common;

interface FormattableUnitInterface
{
    /**
     * @return int
     */
    public function getDecimals();

    /**
     * @return string value in base unit is given as 'd'
     */
    public function getJavaScriptConversion();
}
