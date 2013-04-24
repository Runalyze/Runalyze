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
		if ($this->Training->hasArrayPace())
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
		echo '<strong class="small">Rundenzeiten:&nbsp;</strong>';
		echo '<small class="right margin-5">'.implode(' | ', $this->links()).'</small>';

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