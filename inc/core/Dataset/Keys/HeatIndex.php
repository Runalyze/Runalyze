<?php
/**
 * This file contains class::HeatIndex
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: HeatIndex
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Dataset\Keys
 */
class HeatIndex extends AbstractKey
{
    /**
     * Enum id
     * @return int
     */
    public function id()
    {
        return \Runalyze\Dataset\Keys::HEAT_INDEX;
    }

    /**
     * Database key
     * @return array
     */
    public function column()
    {
        return ['temperature', 'humidity'];
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function label()
    {
        return __('Heat index');
    }

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('HI');
	}

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function description()
    {
        return __(
            'The heat index is a measure of how hot it really feels when relative humidity is factored with the actual air temperature.'
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
            !$context->activity()->weather()->humidity()->isUnknown() &&
            !($context->hasSport() && !$context->sport()->isOutside())
        ) {
            return $context->dataview()->heatIndex()->getIcon();
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