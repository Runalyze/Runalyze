<?php
/**
 * This file contains class::DataCollector
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\Model\Swimdata\Entity as Swimdata;
use Runalyze\Model\Swimdata\Loop;
use Runalyze\Configuration;

/**
 * Collect data from trackdata
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DataCollectorWithSwimdata extends DataCollector {
	/**
	 * @var \Runalyze\Model\Swimdata\Loop;
	 */
	protected $LoopSwimdata;

	/**
	 * Construct collector
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param int $key
	 * @param \Runalyze\Model\Swimdata\Entity $swimdata
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata $trackdata, $key, Swimdata $swimdata) {
		if (!$swimdata->has($key)) {
			throw new \InvalidArgumentException('Swimdata has no data for "'.$key.'".');
		}

		$this->Key = $key;
		$this->Precision = Configuration::ActivityView()->plotPrecision();
		$this->KnowsDistance = $trackdata->has(Trackdata::DISTANCE);

		$this->init($trackdata);
		$this->LoopSwimdata = new Loop($swimdata);
		$this->collect();
	}

	/**
	 * Collect data
	 */
	protected function collect() {
		do {
			$this->move();

			$value = $this->LoopSwimdata->average($this->Key);

			if ($this->XAxis == self::X_AXIS_DISTANCE) {
				$this->Data[(string)$this->Loop->current(Trackdata::DISTANCE)] = $value;
			} elseif ($this->XAxis == self::X_AXIS_TIME) {
				$this->Data[(string)$this->Loop->current(Trackdata::TIME).'000'] = $value;
			} else {
				$this->Data[] = $value;
			}
		} while (!$this->Loop->isAtEnd());
	}

	/**
	 * Get next step for plot data
	 * @return bool 
	 */
	protected function move() {
		parent::move();

		$this->LoopSwimdata->goToIndex( $this->Loop->index() );
	}
}