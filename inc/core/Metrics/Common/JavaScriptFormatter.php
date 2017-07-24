<?php

namespace Runalyze\Metrics\Common;

use Runalyze\Metrics\Velocity\Unit\AbstractPaceInTimeFormatUnit;

class JavaScriptFormatter
{
    public static function getFormatter(UnitInterface $unit)
    {
        if ($unit instanceof AbstractPaceInTimeFormatUnit) {
            $valueConversion = 'Math.floor(Math.round(d)/60) + \':\' + (Math.round(d)%60 < 10 ? \'0\' : \'\') + Math.round(d)%60';
        } elseif ($unit instanceof FormattableUnitInterface) {
            $valueConversion = '(d).toFixed('.$unit->getDecimals().')';
        } elseif (method_exists($unit, 'getJavaScriptConversion')) {
            $valueConversion = $unit->getJavaScriptConversion();
        } else {
            $valueConversion = 'Math.round(d*100)/100';
        }

        return 'function(d){return '.$valueConversion.' + \' '.$unit->getAppendix().'\';}';
    }

    public static function getTransformer(UnitInterface $unit)
    {
        if (method_exists($unit, 'getFactorFromBaseUnit')) {
            $valueConversion = 'd*'.$unit->getFactorFromBaseUnit();
        } elseif (method_exists($unit, 'getDividendFromBaseUnit')) {
            $valueConversion = 'd==0?0:'.$unit->getDividendFromBaseUnit().'/d';
        } elseif (1.234 == $unit->toBaseUnit(1.234)) { // check for base units
            $valueConversion = 'd';
        } else {
            throw new \InvalidArgumentException('Unsupported unit. Transformation rule can\'t be determined.');
        }

        return 'function(d){return '.$valueConversion.';}';
    }
}
