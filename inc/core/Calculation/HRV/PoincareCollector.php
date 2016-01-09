<?php
/**
 * This file contains class::PoincareCollector
 * @package Runalyze\Calculation\HRV
 */

namespace Runalyze\Calculation\HRV;

use Runalyze\Model\HRV\Entity;

/**
 * Create data for a poincare plot
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\HRV
 */
class PoincareCollector {
	/**
	 * @var \Runalyze\Model\HRV\Entity
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
	 * @param \Runalyze\Model\HRV\Entity $hrvObject
	 * @param int $filter [optional] maximal difference to include in plot
	 */
	public function __construct(Entity $hrvObject, $filter = 200) {
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
	 * @return array
	 */
	public function data() {
		return $this->PlotData;
	}
}