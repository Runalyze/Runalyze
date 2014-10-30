<?php
/**
 * This file contains class::DataCollector
 * @package Runalyze\Calculation\Trimp
 */

namespace Runalyze\Calculation\Trimp;

use Runalyze\Calculation\Distribution\Empirical;
use Runalyze\Calculation\Distribution\TimeSeries;

/**
 * Data collector
 * 
 * This data collector builds the appropriate array for a trimp calculator.
 * 
 * Example:
 * <code>
 * $Collector = new DataCollector($HeartRateArray);
 * print_r( $Collector->result() );
 * </code>
 * will for example result in
 * <pre>
 * array(
 *  [120] => 15,
 *  [121] => 27,
 *  [122] => 5,
 *  ...
 * )
 * </pre>
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Trimp
 */
class DataCollector {
	/**
	 * Heart rate
	 * @var array
	 */
	protected $HeartRate;

	/**
	 * Duration
	 * @var array
	 */
	protected $Duration;

	/**
	 * Result
	 * @var array
	 */
	protected $Result;

	/**
	 * Construct
	 * 
	 * Duration array may be empty. In that case equalsized steps of 1s are assumed
	 * 
	 * @param array $HeartRate bpm[]
	 * @param array $Duration [optional] ascending time in [s]
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $HeartRate, array $Duration = array()) {
		$this->HeartRate = $HeartRate;
		$this->Duration = $Duration;

		if (empty($HeartRate)) {
			throw new \InvalidArgumentException('Heart rate array must not be empty.');
		}

		if (!empty($Duration) && count($HeartRate) != count($Duration)) {
			throw new \InvalidArgumentException('Heart rate and duration array must be of equal size.');
		}

		$this->calculate();
	}

	/**
	 * Calculate
	 */
	protected function calculate() {
		$Distribution = empty($this->Duration)
				? new Empirical($this->HeartRate)
				: new TimeSeries($this->HeartRate, $this->Duration);

		$this->Result = $Distribution->histogram();
	}

	/**
	 * Result
	 * @return array
	 */
	public function result() {
		return $this->Result;
	}
}