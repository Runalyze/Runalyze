<?php
/**
 * This file contains class::TrainingPlotsList
 * @package Runalyze\Draw\Training
 */
/**
 * General class for creating all plots for a given training and displaying them
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotsList {
	/**
	 * Array with all plots
	 * @var array
	 */
	protected $Plots = array();

	/**
	 * Construct new list of plots
	 * @param TrainingObject $Training 
	 */
	public function __construct(TrainingObject &$Training) {
		$Collection = (CONF_TRAINING_PLOT_MODE == 'collection');
		$PacePulse  = (CONF_TRAINING_PLOT_MODE == 'pacepulse');

		if ($Collection && !$Training->hasArrayAltitude()) {
			$Collection = false;
			$PacePulse  = true;
		}
		if ($PacePulse && (!$Training->hasArrayPace() || !$Training->hasArrayHeartrate()))
			$PacePulse  = false;

		if (!$Training->Splits()->areEmpty()  && $Training->Splits()->totalDistance() > 0)
			$this->Plots[] = new TrainingPlotLapsManual($Training);
		if ($Collection)
			$this->Plots[] = new TrainingPlotCollection($Training);
		if ($PacePulse)
			$this->Plots[] = new TrainingPlotPacePulse($Training);
		if ($Training->hasArrayPace() && !$PacePulse && !$Collection)
			$this->Plots[] = new TrainingPlotPace($Training);
		if ($Training->hasArrayHeartrate() && !$PacePulse && !$Collection)
			$this->Plots[] = new TrainingPlotPulse($Training);
		if ($Training->hasArrayAltitude() && !$Collection)
			$this->Plots[] = new TrainingPlotElevation($Training);
		if ($Training->hasArrayCadence())
			$this->Plots[] = new TrainingPlotCadence($Training);
		if ($Training->hasArrayPower())
			$this->Plots[] = new TrainingPlotPower($Training);
		if ($Training->hasArrayTemperature())
			$this->Plots[] = new TrainingPlotTemperature($Training);

		if ($Training->Splits()->areEmpty() && $Training->hasArrayPace())
			$this->Plots[] = new TrainingPlotLapsComputed($Training);
	}

	/**
	 * Is this list empty?
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->Plots);
	}

	/**
	 * Display all plots 
	 */
	public function displayAllPlots() {
		foreach ($this->Plots as $Plot) {
			echo '<div id="plot-'.$Plot->getKey().'" class="plot-container">';
			$Plot->display();
			echo '</div>';
		}
	}

	/**
	 * Display all labels 
	 */
	public function displayLabels() {
		foreach ($this->Plots as $Plot)
			echo '<label id="training-view-toggler-'.$Plot->getKey().'" class="checkable" onclick="RunalyzePlot.toggleTrainingChart(\''.$Plot->getKey().'\');"><i id="toggle-'.$Plot->getKey().'" class="toggle-icon-'.$Plot->getKey().' checked"></i> '.$Plot->getTitle().'</label>';
	}

	/**
	 * Display JS code 
	 */
	public function displayJScode() {
		$JScode = 'RunalyzePlot.initTrainingNavitation();';

		foreach ($this->Plots as $Plot)
			if (!$Plot->isVisible())
				$JScode .= '$("#training-view-toggler-'.$Plot->getKey().'").click();';

		echo Ajax::wrapJSforDocumentReady($JScode);
	}
}