<?php
/**
 * This file contains class::WindChill
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: WindChill
 *
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class WindChill extends AbstractKey
{
    /**
     * Enum id
     * @return int
     */
    public function id()
    {
        return \Runalyze\Dataset\Keys::WIND_CHILL;
    }

    /**
     * Database key
     * @return array
     */
    public function column()
    {
        return ['temperature', 'wind_speed', 'distance', 's'];
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function label()
    {
        return __('Windchill');
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function description()
    {
        return __(
            'Windchill is the perceived air temperature felt by the body on exposed skin due to the flow of air.'
        );
    }

    /**
     * Get string to display this dataset value
     * @param \Runalyze\Dataset\Context $context
     * @return string
     */
    public function stringFor(Context $context)
    {
        if (
            !$context->activity()->weather()->temperature()->isUnknown() &&
            !$context->activity()->weather()->windSpeed()->isUnknown() &&
            !($context->hasSport() && !$context->sport()->isOutside())
        ) {
            return $context->dataview()->windChillFactor()->string();
        }

        return '';
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function cssClass()
    {
        return 'small';
    }
}