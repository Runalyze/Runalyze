<?php
/**
 * This file contains class::Empirical
 * @package Runalyze\Calculation\Distribution
 */

namespace Runalyze\Calculation\Distribution;

/**
 * Empirical distribution
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Distribution
 */
class Empirical extends Distribution {
	/**
	 * Data as histogram
	 * @var array
	 */
	protected $Histogram = array();

	/**
	 * Construct empirical distribution
	 * @param array $data array of data points
	 */
	public function __construct(array $data) {
		$this->Histogram = array_count_values($data);
	}

	/**
	 * Histogram data
	 * @return array
	 */
	public function histogram() {
		return $this->Histogram;
	}
}