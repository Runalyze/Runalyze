<?php
/**
 * This file contains class::TrainingObject
 * @package Runalyze\DataObjects\Training
 */
/**
 * DataObject for trainings
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training
 */
class TrainingDeleter {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $TrainingObject = null;

	/**
	 * Constructor
	 * @param int $TrainingID
	 */
	public function __construct($TrainingID) {
		$this->TrainingObject = new TrainingObject($TrainingID);

		if ($this->TrainingObject->isDefaultId()) {
			throw new InvalidArgumentException('TrainingDeleter could not be instantiated.');
		}
	}

	/**
	 * Delete training
	 */
	public function delete() {
		$this->removeFromDatabase();
		$this->recalculateStatistics();
	}

	/**
	 * Remove row from database
	 */
	protected function removeFromDatabase() {
		DB::getInstance()->deleteByID('training', $this->TrainingObject->id());
	}

	/**
	 * Recalculate statistics
	 */
	protected function recalculateStatistics() {
		$this->recalculateMaxTrimpValues();
		$this->recalculateVDOTshape();
		$this->recalculateBasicEndurance();

		$this->recalculateShoeStatistics();
		$this->recalculateStartTime();
	}

	/**
	 * Recalculate max trimp values
	 */
	protected function recalculateMaxTrimpValues() {
		Trimp::calculateMaxValues();
	}

	/**
	 * Recalculate VDOT shape
	 */
	protected function recalculateVDOTshape() {
		if ($this->TrainingObject->Sport()->isRunning() && $this->TrainingObject->getTimestamp() >= (time() - Configuration::Vdot()->days() * DAY_IN_S) ) {
			JD::recalculateVDOTform();
		}
	}

	/**
	 * Recalculate basic endurance
	 * 
	 * @see BasicEndurance::$DAYS_FOR_WEEK_KM
	 */
	protected function recalculateBasicEndurance() {
		if ($this->TrainingObject->Sport()->isRunning() && $this->TrainingObject->getTimestamp() >= (time() - 182 * DAY_IN_S) ) {
			BasicEndurance::recalculateValue();
		}
	}

	/**
	 * Recalculate shoe statistics
	 */
	protected function recalculateShoeStatistics() {
		if (!$this->TrainingObject->Shoe()->isDefaultId()) {
			DB::getInstance()->exec('
				UPDATE `'.PREFIX.'shoe`
				SET
					`km`=`km`+'.$this->TrainingObject->getDistance().',
					`time`=`time`+'.$this->TrainingObject->getTimeInSeconds().'
				WHERE `id`='.$this->TrainingObject->Shoe()->id().' LIMIT 1
			');
		}
	}

	/**
	 * Recalculate start time
	 */
	protected function recalculateStartTime() {
		if (START_TIME >= $this->TrainingObject->getTimestamp()) {
			Helper::recalculateStartTime();
		}
	}
}