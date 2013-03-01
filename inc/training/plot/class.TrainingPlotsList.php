<?php
/**
 * Class: TrainingPlotsList
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingPlotsList {
	/**
	 * Array with all plots
	 * @var array
	 */
	protected $Plots = array();

	/**
	 * Construct new list of plots
	 * @param Training $Training 
	 */
	public function __construct(Training &$Training) {
		$Collection = (CONF_TRAINING_PLOT_MODE == 'collection');
		$PacePulse  = (CONF_TRAINING_PLOT_MODE == 'pacepulse');

		if ($Collection && !$Training->hasElevationData()) {
			$Collection = false;
			$PacePulse  = true;
		}
		if ($PacePulse && (!$Training->hasPaceData() || !$Training->hasPulseData()))
			$PacePulse  = false;

		if ($Training->hasSplits())
			$this->Plots[] = new TrainingPlotSplits($Training);
		if ($Collection)
			$this->Plots[] = new TrainingPlotCollection($Training);
		if ($PacePulse)
			$this->Plots[] = new TrainingPlotPacePulse($Training);
		if ($Training->hasPaceData() && !$PacePulse && !$Collection)
			$this->Plots[] = new TrainingPlotPace($Training);
		if ($Training->hasPulseData() && !$PacePulse && !$Collection)
			$this->Plots[] = new TrainingPlotPulse($Training);
		if ($Training->hasElevationData() && !$Collection)
			$this->Plots[] = new TrainingPlotElevation($Training);

		if (!$Training->hasSplits() && $Training->hasPaceData())
			$this->Plots[] = new TrainingPlotSplits($Training);
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
			echo '</div>'.NL;
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