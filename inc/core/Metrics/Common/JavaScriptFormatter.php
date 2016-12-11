<?php

namespace Runalyze\Metrics\Common;

use Runalyze\Metrics\Pace\Unit\AbstractPaceInTimeFormatUnit;

class JavaScriptFormatter
{
    public static function getFormatter(UnitInterface $unit)
    {
        if ($unit instanceof AbstractPaceInTimeFormatUnit) {
            $valueConversion = 'Math.floor(d/60) + \':\' + (Math.round(d%60) < 10 ? \'0\' : \'\') + Math.round(d%60)';
        } elseif ($unit instanceof FormattableUnitInterface) {
            $valueConversion = '('.$unit->getJavaScriptConversion().').toFixed('.$unit->getDecimals().')';
        } elseif (method_exists($unit, 'getJavaScriptConversion')) {
            $valueConversion = $unit->getJavaScriptConversion();
        } else {
            $valueConversion = 'd';
        }

        return 'function(d){return '.$valueConversion.' + \' '.$unit->getAppendix().'\';}';
    }
}
