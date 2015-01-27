<?php
/**
 * This file contains class::ActivitySeries
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\View\Activity\Plot\Series\DataCollector;
use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\View\Activity\Context;

use \Plot;

/**
 * Activity series
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
abstract class ActivitySeries extends \Runalyze\View\Plot\Series {
	/**
	 * @var enum
	 */
	protected $XAxis = 0;

	/**
	 * @var \Runalyze\View\Activity\Context $context
	 */
	abstract public function __construct(Context $context);

	/**
	 * Init data
	 * @param \Runalyze\Model\Trackdata $trackdata
	 * @param string $key
	 */
	protected function initData(Trackdata $trackdata, $key) {
		if (!$trackdata->has($key)) {
			$this->Data = array();
			return;
		}

		$Collector = new DataCollector($trackdata, $key);

		$this->Data = $Collector->data();
		$this->XAxis = $Collector->xAxis();
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(Plot &$Plot, $yAxis, $addAnnotations = true) {
		parent::addTo($Plot, $yAxis, $addAnnotations);

		switch ($this->XAxis) {
			case DataCollector::X_AXIS_DISTANCE:
				$Plot->setXUnit('km');
				break;

			case DataCollector::X_AXIS_TIME:
				$Plot->setXAxisAsTime();
				$Plot->setXAxisTimeFormat("%h:%M:%S");
				$Plot->Options['xaxis']['ticks'] = 5;
				break;

			case DataCollector::X_AXIS_INDEX:
				$Plot->hideXLabels();
		}
	}
}