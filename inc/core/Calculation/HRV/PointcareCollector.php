<?php
/**
 * This file contains class::PointcareCollector
 * @package Runalyze\Calculation\HRV
 */

namespace Runalyze\Calculation\HRV;

use Runalyze\Model\HRV\Object;

/**
 * Create data for a pointcare plot
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\HRV
 */
class PointcareCollector {
	/**
	 * @var \Runalyze\Model\HRV\Object
	 */
	protected $Object;

	/**
	 * Filter: maximal difference to include in plot
	 * @var int
	 */
	protected $Filter = 0;

	/**
	 * Plot data
	 * @var array
	 */
	protected $PlotData = array();

	/**
	 * Calculator for hrv statistics
	 * @param \Runalyze\Model\HRV\Object $hrvObject
	 * @param int $filter [optional] maximal difference to include in plot
	 */
	public function __construct(Object $hrvObject, $filter = 200) {
		$this->Object = $hrvObject;
		$this->Filter = $filter;

		$this->collectPlotData();
	}

	/**
	 * Collect plot data
	 */
	protected function collectPlotData() {
		$data = $this->Object->data();
		$num = $this->Object->num();

		for ($i = 1; $i < $num; ++$i) {
			if (abs($data[$i-1] - $data[$i]) < $this->Filter) {
				$this->PlotData[(string)$data[$i-1]] = $data[$i];
			}
		}
	}

	/**
	 * Plot data
	 * @var array
	 */
	public function data() {
		return $this->PlotData;
	}
}