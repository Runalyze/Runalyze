<?php
/**
 * This file contains class::ParserAbstractMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Abstract parser for multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
abstract class ParserAbstractMultiple extends ParserAbstract {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	private $TrainingObjects = array();

	/**
	 * Get training objects
	 * @return array
	 */
	final public function objects() {
		return $this->TrainingObjects;
	}

	/**
	 * Get training object
	 * @param int $index optional index
	 * @return TrainingObject
	 */
	final public function object($index = 0) {
		if (!isset($this->TrainingObjects[$index])) {
			Error::getInstance()->addDebug('Parser has only '.$this->numberOfTrainings().' trainings, but asked for index = '.$index);
			return end($this->TrainingObjects);
		}

		return $this->TrainingObjects[$index];
	}

	/**
	 * Number of trainings parsed
	 * @return int
	 */
	final public function numberOfTrainings() {
		return count($this->TrainingObjects);
	}

	/**
	 * Add training object from single parser
	 * @param TrainingObject $Object training object
	 */
	final protected function addObject(TrainingObject $Object) {
		$this->TrainingObjects[] = $Object;
	}
}