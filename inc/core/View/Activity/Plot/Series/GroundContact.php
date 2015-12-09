<?php
/**
 * This file contains class::GroundContact
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

use \Plot;

/**
 * Plot for: ground contact time
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class GroundContact extends ActivityPointSeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(200,100,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::GROUNDCONTACT);
		$this->setManualAverage($context->activity()->groundcontact());
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Ground contact');
		$this->Color = self::COLOR;

		$this->UnitString = 'ms';
		$this->UnitDecimals = 0;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(Plot &$Plot, $yAxis, $addAnnotations = true) {
		parent::addTo($Plot, $yAxis, $addAnnotations);

		$this->setColorThresholdsBelow($Plot, 208, 240, 272, 305);
	}
}