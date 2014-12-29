<?php
/**
 * This file contains class::ActivityPlot
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

use Plot;

/**
 * Plot for activity data
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
abstract class ActivityPlot {
	/**
	 * @var int
	 */
	const WIDTH = 600;

	/**
	 * @var int
	 */
	const HEIGHT = 190;

	/**
	 * Plot
	 * @var \Plot
	 */
	protected $Plot;

	/**
	 * Use standard x-axis?
	 * @var boolean
	 */
	protected $useStandardXaxis = true;

	/**
	 * Key
	 * @var string
	 */
	protected $key = '';

	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * CSS id
	 * @var string
	 */
	protected $id;

	/**
	 * Set key
	 */
	abstract protected function setKey();

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	abstract protected function initData(Activity\Context $context);

	/**
	 * Construct
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->setKey();
		$this->setID($context->activity()->id());
		$this->initPlot();
		$this->initData($context);

		if (!$this->dataIsAvailable()) {
			$this->Plot->raiseError( __('No plot available.') );
		}
	}

	/**
	 * Add series
	 * @param \Runalyze\View\Activity\Plot\Series\ActivitySeries $series
	 * @param int $yAxis
	 * @param boolean $addAnnotations
	 */
	final protected function addSeries(Series\ActivitySeries $series, $yAxis = 1, $addAnnotations = true) {
		$series->addTo($this->Plot, $yAxis, $addAnnotations);
	}

	/**
	 * Data is available
	 * @return bool
	 */
	private function dataIsAvailable() {
		foreach ($this->Plot->Data as $key => $Data) {
			if (!isset($Data['data']) || empty($Data['data'])) {
				unset($this->Plot->Data[$key]);
			}
		}

		return (count($this->Data) >= 2 || !empty($this->Plot->Data));
	}

	/**
	 * Init Plot 
	 */
	private function initPlot() {
		$this->Plot = new Plot($this->getCSSid(), self::WIDTH, self::HEIGHT);
	}

	/**
	 * Set ID
	 * @param int $id
	 */
	private function setID($id) {
		$this->id = $this->key.'_'.$id;
	}

	/**
	 * Get key
	 * @return string
	 */
	final public function getKey() {
		return $this->key;
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	final protected function getCSSid() {
		return $this->id;
	}

	/**
	 * Output JS 
	 */
	final public function display() {
		echo Plot::getInnerDivFor($this->getCSSid(), self::WIDTH, self::HEIGHT);
		$this->Plot->outputJavaScript();
	}
}