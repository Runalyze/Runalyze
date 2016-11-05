<?php

namespace Runalyze\Data\Weather;

use Runalyze\Util\AbstractEnum;
use Ajax;

/**
 * Heat index effect as defined by the U.S. National Oceanic and Atmospheric Administration
 * 
 * @see http://www.nws.noaa.gov/os/heat/heat_index.shtml
 */
class HeatIndexEffect extends AbstractEnum
{
    /** @var int */
    const NO_EFFECT = 0;
    
    /** @var int */
    const CAUTION = 1;
    
    /** @var int */
    const EXTREME_CAUTION = 2;
    
    /** @var int */
    const DANGER = 3;
    
    /** @var int */
    const EXTREME_DANGER = 4;

    /**
     * @param float $value value (in Fahrenheit)
     * @return int internal enum
     * @throws \InvalidArgumentException
     */
    public static function levelFor($value)
    {
        if ($value >= 130) {
            return self::EXTREME_DANGER;
        } elseif ($value >= 105) {
            return self::DANGER;
        } elseif ($value >= 91) {
            return self::EXTREME_CAUTION;
        } elseif ($value >= 80) {
            return self::CAUTION;
        }
        
        return self::NO_EFFECT;
    }
    
    /**
     * @param int $enum internal enum
     * @return string
     * @throws \InvalidArgumentException
     * @codeCoverageIgnore
     */
    public static function label($enum)
    {
        switch ($enum) {
            case self::NO_EFFECT:
                return __('No effect');
            case self::CAUTION:
                return __('Caution');
            case self::EXTREME_CAUTION:
                return __('Extreme caution');
            case self::DANGER:
                return __('Danger');
            case self::EXTREME_DANGER:
                return __('Extreme danger');
            default:
                throw new \InvalidArgumentException(sprintf('Provided level %u is invalid.', $enum));
        }
    }

    /**
     * @param int $enum internal enum
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function colorFor($enum)
    {
        switch ($enum) {
            case self::NO_EFFECT:
                return '#32ad32';
            case self::CAUTION:
                return '#ffff00';
            case self::EXTREME_CAUTION:
                return '#ffd700';
            case self::DANGER:
                return '#ffa500';
            case self::EXTREME_DANGER:
                return '#ff0000';
            default:
                return '#32ad32';
        }
    }

    /**
     * @param int $enum internal enum
     * @return string
     * @throws \InvalidArgumentException
     * @codeCoverageIgnore
     */
    public static function description($enum)
    {
        switch ($enum) {
            case self::NO_EFFECT:
                return __('temperature and humidity should not have any effect');
            case self::CAUTION:
                return __('fatigue is possible with prolonged exposure and activity. Continuing activity could result in heat cramps');
            case self::EXTREME_CAUTION:
                return __('heat cramps and heat exhaustion are possible. Continuing activity could result in heat stroke');
            case self::DANGER:
                return __('heat cramps and heat exhaustion are likely; heat stroke is probable with continued activity');
            case self::EXTREME_DANGER:
                return __('heat stroke is imminent');
            default:
                throw new \InvalidArgumentException(sprintf('Provided level %u is invalid.', $enum));
        }
    }
    
    /**
     * @param int $enum internal enum
     * @return string
     * @codeCoverageIgnore
     */
    public static function icon($enum)
    {
                $code = '<i class="fa fa-fw '.self::fontIconName($enum).'" style="color:'.self::colorFor($enum).'"></i>';
                $Tooltip = new \Runalyze\View\Tooltip(self::label($enum).'<br>'.self::description($enum));
        		$Tooltip->setPosition('atRight');
        		$Tooltip->wrapAround($code);
        		return $code;
    }
    
    /**
     * @param int $enum internal enum
     * @return string
     * @codeCoverageIgnore
     */
    public static function fontIconName($enum)
    {
        switch ($enum) {
            case self::CAUTION:
            case self::EXTREME_CAUTION:
            case self::DANGER:
            case self::EXTREME_DANGER:
                return 'fa-exclamation-triangle';
            default:    
                return 'fa-check';
        }
    }
    
}