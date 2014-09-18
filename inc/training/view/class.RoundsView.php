<?php
/**
 * This file contains class::RoundsView
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display rounds
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class RoundsView {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Array with RoundsAbstract
	 * @var array
	 */
	protected $RoundsObjects = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->init();
	}

	/**
	 * Init
	 */
	private function init() {
		if (!$this->Training->Splits()->areEmpty())
			$this->RoundsObjects[] = new RoundsSplits($this->Training);
		if ($this->Training->hasArrayDistance() && $this->Training->hasArrayTime())
			$this->RoundsObjects[] = new RoundsComputed($this->Training);
	}

	/**
	 * Links as array
	 * @return array
	 */
	private function links() {
		$Links = array();

		foreach ($this->RoundsObjects as $Round)
			$Links[] = Ajax::change($Round->title(), 'training-rounds-container', $Round->key());

		return $Links;
	}

	/**
	 * Display
	 */
	public function display() {
		if (empty($this->RoundsObjects))
			return;

		echo '<div id="training-rounds" class="dataBox left">';
		echo '<div class="databox-header">'.implode(' - ', $this->links()).'</div>';

		$this->displayRounds();

		echo '</div>';
	}

	/**
	 * Display rounds
	 */
	private function displayRounds() {
		echo '<div id="training-rounds-container">';

		foreach ($this->RoundsObjects as $i => $Round) {
			echo '<div id="'.$Round->key().'" class="change" '.($i > 0 ? 'style="display:none;"' : '').'>';
			$Round->display();
			echo '</div>';
		}

		echo '</div>';
	}
}