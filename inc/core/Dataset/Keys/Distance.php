<?php
/**
 * This file contains class::Distance
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\View\Stresscolor;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Dataset key: Distance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Distance extends AbstractKey
{
	/** @var string */
	const KEY_DISTANCE_COMPARISON = 'distance_to_compare_to';

	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::DISTANCE;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'distance';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Distance');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->hasData(self::KEY_DISTANCE_COMPARISON)) {
			return $context->dataview()->distance().
				$this->distanceComparison($context->activity()->distance(), $context->data(self::KEY_DISTANCE_COMPARISON));
		}

		return $context->dataview()->distance();
	}

	/**
	 * Compare distance to last set
	 * @param float $currentDistance [km]
	 * @param float $previousDistance [km]
	 * @return string
	 */
	protected function distanceComparison($currentDistance, $previousDistance) {
		$Percentage = $this->distanceComparisonPercentage($currentDistance, $previousDistance);
		$String = ($Percentage != 0) ? sprintf("%+d", $Percentage).'&nbsp;&#37;' : '-';

		$Stress = new Stresscolor($Percentage);
		$Stress->scale(0, 30);

		return ' <small style="display:inline-block;width:55px;color:#'.$Stress->rgb().'">'.$String.'</small>';
	}

	/**
	 * Get percentage of last distance
	 * @param float $currentDistance [km]
	 * @param float $previousDistance [km]
	 * @return int
	 */
	protected function distanceComparisonPercentage($currentDistance, $previousDistance) {
		if ($previousDistance == 0) {
			return 0;
		}

		return round(100*($currentDistance - $previousDistance) / $previousDistance, 1);
	}

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::SUM;
	}
}