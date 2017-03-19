<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Runalyze\Configuration;

/**
 * Loop through trackdata object
 *
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Loop extends \Runalyze\Model\Loop {
	/**
	 * Object
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Trackdata\Entity $object
	 */
	public function __construct(Entity $object) {
		parent::__construct($object);
	}

	/**
	 * Current time
	 * @return int
	 */
	public function time() {
		return $this->current(Entity::TIME);
	}

	/**
	 * Current distance
	 * @return float
	 */
	public function distance() {
		return $this->current(Entity::DISTANCE);
	}

	/**
	 * Move Distance
	 * @return bool
	 */
	public function nextDistance() {
		if (Configuration::General()->distanceUnitSystem()->isImperial()) {
			return $this->nextMile();
		}

		return $this->nextKilometer();
	}

	/**
	 * Next kilometer
	 *
	 * Alias for <code>moveDistance(1.0)</code>
	 * @return boolean
	 */
	public function nextKilometer() {
		$this->moveDistance(1.0);

		return $this->isAtEnd();
	}

	/**
	 * Next mile
	 *
	 * Alias for <code>moveDistance(1.60934)</code>
	 * @return boolean
	 */
	public function nextMile() {
		$this->moveDistance(1.60934);

		return $this->isAtEnd();
	}

	/**
	 * Move for time
	 * @param int $seconds
	 * @throws \RuntimeException for negative values or if time is empty
	 */
	public function moveTime($seconds) {
		$this->move(Entity::TIME, $seconds);
	}

	/**
	 * Move to time
	 * @param int $seconds
	 * @throws \RuntimeException for negative values or if time is empty
	 */
	public function moveToTime($seconds) {
		$this->moveTo(Entity::TIME, $seconds);
	}

	/**
	 * Move for distance
	 * @param float $kilometer
	 * @throws \RuntimeException for negative values or if distance is empty
	 */
	public function moveDistance($kilometer) {
		$this->move(Entity::DISTANCE, $kilometer);
	}

	/**
	 * Move to distance
	 * @param float $kilometer
	 * @throws \RuntimeException for negative values or if distance is empty
	 */
	public function moveToDistance($kilometer) {
		$this->moveTo(Entity::DISTANCE, $kilometer);
	}

    /**
     * Average value for current section
     * @param string $key
     * @return int
     */
    public function average($key)
    {
        if ($this->LastIndex < $this->Index && $this->Object->has(Entity::TIME) && $this->Object->has($key)) {
            $lastTime = $this->Object->at($this->LastIndex, Entity::TIME);
            $totalTime = 0;
            $sum = 0;

            for ($i = $this->LastIndex + 1; $i <= $this->Index; ++$i) {
                $currentTime = $this->Object->at($i, Entity::TIME);
                $totalTime += $currentTime - $lastTime;

                $sum += $this->Object->at($i, $key) * ($currentTime - $lastTime);
                $lastTime = $currentTime;
            }

            if (0 == $totalTime) {
                return $sum / ($this->Index - $this->LastIndex);
            }

            return $sum / $totalTime;
        }

        return parent::average($key);
    }

	/**
	 * @param array $data
	 * @return \Runalyze\Model\Trackdata\Entity
	 */
	protected function createNewObject(array $data) {
		return new Entity($data);
	}
}
