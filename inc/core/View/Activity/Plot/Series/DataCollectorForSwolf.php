<?php
/**
 * This file contains class::DataCollectorForSwolf
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;
use Runalyze\Model\Swimdata\Object as Trakcdata;
use Runalyze\Model\Swimdata\Object as Swimdata;

/**
 * Collect data from trackdata for stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DataCollectorForStrideLength extends DataCollector {
	/**
	 * Construct collector
	 * @param \Runalyze\Model\Swimdata\Object $Swimdata
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Swimdata $swimdata) {
		parent::__construct($swimdata, Swimdata);
                parent::__construct($trackdata, Trackdata);
	}

	/**
	 * Collect data
	 */
	protected function collect() {
		do {
			$this->move();

			$avgCadence = $this->Loop->average(Swimdata::STROKE);
			$deltaTime = $this->Loop->difference(Trackdata::TIME);

                        //TODO
			if ($this->XAxis == self::X_AXIS_Swolf) {
				$this->Data[(string)$this->Loop->current(Trackdata::DISTANCE)] = $value;
			} else {
				$this->Data[] = $value;
			}
		} while (!$this->Loop->isAtEnd());
	}
}