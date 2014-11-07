<?php
/**
 * This file contains class::Pauses
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

/**
 * Pauses object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Pauses {
	/**
	 * Single objects
	 * @var \Runalyze\Model\Trackdata\Pause[]
	 */
	protected $Objects = array();

	/**
	 * Construct
	 * @param mixed $data string or array
	 */
	public function __construct($data = '') {
		if (is_array($data)) {
			$this->fromArray($data);
		} elseif (!empty($data)) {
			$this->fromString($data);
		}
	}

	/**
	 * From array
	 * @param array[] $data array of arrays for single pauses
	 */
	public function fromArray(array $data) {
		foreach ($data as $array) {
			$Pause = new Pause();
			$Pause->fromArray($array);

			$this->addPause($Pause);
		}
	}

	/**
	 * As array
	 * @return array
	 */
	public function asArray() {
		$Data = array();

		foreach ($this->Objects as $Pause) {
			$Data[] = $Pause->asArray();
		}

		return $Data;
	}

	/**
	 * From string
	 * @param string $string
	 */
	public function fromString($string) {
		$this->fromArray(json_decode($string, true));
	}

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		return json_encode($this->asArray());
	}

	/**
	 * Clear
	 */
	public function clear() {
		$this->Objects = array();
	}

	/**
	 * Number of pauses
	 * @return int
	 */
	public function num() {
		return count($this->Objects);
	}

	/**
	 * Are they empty?
	 * @return bool
	 */
	public function areEmpty() {
		return ($this->num() == 0);
	}

	/**
	 * Add pause
	 * @param \Runalyze\Model\Trackdata\Pause $pause
	 */
	public function addPause(Pause $pause) {
		$this->Objects[] = $pause;
	}

	/**
	 * Get pause
	 * @param int $index
	 * @return \Runalyze\Model\Trackdata\Pause
	 * @throws \InvalidArgumentException
	 */
	public function at($index) {
		if (!isset($this->Objects[$index])) {
			throw new \InvalidArgumentException('Unknown object index.');
		}

		return $this->Objects[$index];
	}
}