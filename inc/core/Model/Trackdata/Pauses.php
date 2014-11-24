<?php
/**
 * This file contains class::Pauses
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Runalyze\Model\StringArrayObject;

/**
 * Pauses object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Pauses extends StringArrayObject {
	/**
	 * Single objects
	 * @var \Runalyze\Model\Trackdata\Pause[]
	 */
	protected $Elements = array();

	/**
	 * From array
	 * @param array[] $data array of arrays for single pauses
	 */
	public function fromArray(array $data) {
		foreach ($data as $array) {
			$Pause = new Pause();
			$Pause->fromArray($array);

			$this->add($Pause);
		}
	}

	/**
	 * As array
	 * @return array
	 */
	public function asArray() {
		$Data = array();

		foreach ($this->Elements as $Pause) {
			$Data[] = $Pause->asArray();
		}

		return $Data;
	}

	/**
	 * Add pause
	 * @param \Runalyze\Model\Trackdata\Pause $pause
	 */
	public function add(Pause $pause) {
		parent::add($pause);
	}

	/**
	 * Get pause
	 * @param int $index
	 * @return \Runalyze\Model\Trackdata\Pause
	 * @throws \InvalidArgumentException
	 */
	public function at($index) {
		return parent::at($index);
	}
}