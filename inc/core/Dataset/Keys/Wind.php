<?php
/**
 * This file contains class::Wind
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\View\Icon\WindIcon;

/**
 * Dataset key: Wind
 *
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Wind extends AbstractKey
{
    /**
     * Enum id
     * @return int
     */
    public function id()
    {
        return \Runalyze\Dataset\Keys::WIND;
    }

    /**
     * Database key
     * @return array
     */
    public function column()
    {
        return ['wind_speed', 'wind_deg'];
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function label()
    {
        return __('Wind');
    }

    /**
     * Get string to display this dataset value
     * @param \Runalyze\Dataset\Context $context
     * @return string
     */
    public function stringFor(Context $context)
    {
        $weather = $context->activity()->weather();

        if (
            (!$weather->windSpeed()->isUnknown() || !$weather->windDegree()->isUnknown()) &&
            !($context->hasSport() && !$context->sport()->isOutside())
        ) {
            return (new WindIcon($weather->windSpeed(), $weather->windDegree()))->code();
        }

        return '';
    }
}