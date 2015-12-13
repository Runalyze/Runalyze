<?php
/**
 * This file contains class::DataCollectorForStrideLength
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;

/**
 * Collect data from trackdata for stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DataCollectorForStrideLength extends DataCollector {
	/**
	 * Construct collector
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata $trackdata) {
		parent::__construct($trackdata, Trackdata::CADENCE);
	}

	/**
	 * Collect data
	 */
	protected function collect() {
		do {
			$this->move();

			$avgCadence = $this->Loop->average(Trackdata::CADENCE);
			$deltaTime = $this->Loop->difference(Trackdata::TIME);
			$deltaDist = $this->Loop->difference(Trackdata::DISTANCE);

			if ($avgCadence > 0 && $deltaTime > 0) {
				$value = round( $deltaDist * 1000 * 100 / ($avgCadence * 2 / 60 * $deltaTime) );
			} else {
				$value = 0;
			}

			if ($this->XAxis == self::X_AXIS_DISTANCE) {
				$this->Data[(string)$this->Loop->current(Trackdata::DISTANCE)] = $value;
			} elseif ($this->XAxis == self::X_AXIS_TIME) {
				$this->Data[(string)$this->Loop->current(Trackdata::TIME).'000'] = $value;
			} else {
				$this->Data[] = $value;
			}
		} while (!$this->Loop->isAtEnd());
	}
}