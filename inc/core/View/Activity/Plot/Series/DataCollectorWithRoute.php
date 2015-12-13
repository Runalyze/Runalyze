<?php
/**
 * This file contains class::DataCollector
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\Model\Route\Entity as Route;
use Runalyze\Model\Route\Loop;
use Runalyze\Configuration;

/**
 * Collect data from trackdata
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DataCollectorWithRoute extends DataCollector {
	/**
	 * @var \Runalyze\Model\Route\Loop;
	 */
	protected $LoopRoute;

	/**
	 * Construct collector
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param int $key
	 * @param \Runalyze\Model\Route\Entity $route
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata $trackdata, $key, Route $route) {
		if (!$route->has($key)) {
			throw new \InvalidArgumentException('Route has no data for "'.$key.'".');
		}

		$this->Key = $key;
		$this->Precision = Configuration::ActivityView()->plotPrecision();
		$this->KnowsDistance = $trackdata->has(Trackdata::DISTANCE);

		$this->init($trackdata);
		$this->LoopRoute = new Loop($route);
		$this->collect();
	}

	/**
	 * Collect data
	 */
	protected function collect() {
		do {
			$this->move();

			$value = $this->LoopRoute->average($this->Key);

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

		$this->LoopRoute->goToIndex( $this->Loop->index() );
	}
}