<?php
/**
 * This file contains class::ElevationInfo
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display elevation info for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class ElevationInfo {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Training = null;

	/**
	 * Lowest point
	 * @var int
	 */
	protected $lowestPoint = 0;

	/**
	 * Highest point
	 * @var int
	 */
	protected $highestPoint = 0;

	/**
	 * Manual elevation
	 * @var int
	 */
	protected $manualElevation = 0;

	/**
	 * Calculated elevation
	 * @var int
	 */
	protected $calculatedElevation = 0;

	/**
	 * Constructor
	 * @param TrainingObject $Training Training object
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;
	}

	/**
	 * Display
	 */
	public function display() {
		$this->calculateValues();

		$this->displayHeader();
		$this->displayStandardValues();
	}

	/**
	 * Calculate values
	 */
	protected function calculateValues() {
		$this->lowestPoint = min( $this->Training->getArrayAltitude() );
		$this->highestPoint = max( $this->Training->getArrayAltitude() );

		$this->manualElevation = $this->Training->getElevation();
		$this->calculatedElevation = $this->Training->GpsData()->calculateElevation();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1('H&ouml;henmeter-Berechnung zum '.$this->Training->DataView()->getTitleWithCommentAndDate());
	}

	/**
	 * Display standard values
	 */
	protected function displayStandardValues() {
		$Fieldset = new FormularFieldset('Allgemeine Daten');
		$Fieldset->setHtmlCode('
			<div class="w50">
				<label>manueller Wert</label>
				<span class="asInput">'.$this->manualElevation.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>niedrigster Punkt</label>
				<span class="asInput">'.$this->lowestPoint.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>berechneter Wert</label>
				<span class="asInput">'.$this->calculatedElevation.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>h&ouml;chster Punkt</label>
				<span class="asInput">'.$this->highestPoint.'&nbsp;m</span>
			</div>
		');
		$Fieldset->display();
	}
}