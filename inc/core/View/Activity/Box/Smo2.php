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
 * Boxed value for Smo2
 * 
 * @author Michael Pohl
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class Smo2 extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(Context $Context, $sensorIndex = 0)
	{
        if ($sensorIndex == 1) {
            $trackdataKey = Trackdata\Entity::SMO2_1;
            $label = _('Smo2'). ' (2)';
        } else {
            $trackdataKey = Trackdata\Entity::SMO2_0;
            $label = _('Smo2');
        }

        $value = new TrackdataAverages($Context->trackdata(), [$trackdataKey]);
		parent::__construct(
            round($value->average($trackdataKey),2),
			'%',
			$label
		);
		$this->defineAsFloatingBlock('w50');
	}
}
