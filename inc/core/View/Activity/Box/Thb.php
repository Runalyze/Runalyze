<?php
/**
 * This file contains class::RPE
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;
use Runalyze\Model\Trackdata;
use Runalyze\Calculation\Distribution\TrackdataAverages;

/**
 * Boxed value for Thb
 * 
 * @author Michael Pohl
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Thb extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context, $sensorIndex = 0)
	{
        if ($sensorIndex == 1) {
            $trackdataKey = Trackdata\Entity::THB_1;
            $label = _('Thb (2)');
        } else {
            $trackdataKey = Trackdata\Entity::THB_0;
            $label = _('Thb');
        }

        $value = new TrackdataAverages($Context->trackdata(), [$trackdataKey]);
		parent::__construct(
            round($value->average($trackdataKey), 2),
			'g/dL',
			$label
		);
		$this->defineAsFloatingBlock('w50');
	}
}