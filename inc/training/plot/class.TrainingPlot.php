<?php
/**
 * This file contains class::TrainingPlot
 * @package Runalyze\Draw\Training
 */
/**
 * Abstract class for training plots
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
abstract class TrainingPlot {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Plot
	 * @var Plot
	 */
	protected $Plot = null;

	/**
	 * (Initial) Width
	 * @var int
	 */
	protected $width = 480;

	/**
	 * Height
	 * @var int
	 */
	protected $height = 190;

	/**
	 * Is tracking-mode enabled?
	 * @var boolean
	 */
	protected $tracking = true;

	/**
	 * Is selection-mode enabled?
	 * @var boolean
	 */
	protected $selecting = true;

	/**
	 * Is zooming-mode enabled?
	 * @var boolean
	 */
	protected $zooming = false;

	/**
	 * Show legend?
	 * @var boolean
	 */
	protected $showLegend = false;

	/**
	 * Use standard x-axis?
	 * @var boolean
	 */
	protected $useStandardXaxis = true;

	/**
	 * Key
	 * @var string
	 */
	protected $key = '';

	/**
	 * Title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Is this plot visible?
	 * @return string 
	 */
	abstract public function isVisible();

	/**
	 * Set key and title for this plot
	 */
	abstract protected function setKeyAndTitle();

	/**
	 * Init data
	 */
	abstract protected function initData();

	/**
	 * Set all properties for this plot 
	 */
	abstract protected function setProperties();

	/**
	 * Construct a new TrainingPlot
	 * @param TrainingObject $TrainingObject
	 */
	public function __construct(TrainingObject &$TrainingObject) {
		$this->Training   = $TrainingObject;

		$this->setKeyAndTitle();
		$this->initPlot();
		$this->initData();

		if (empty($this->Data) && empty($this->Plot->Data)) {
			$this->Plot->raiseError('No data.');
		} else {
			$this->setProperties();
			$this->setDependingProperties();
		}
	}

	/**
	 * Get key
	 * @return string
	 */
	final public function getKey() {
		return $this->key;
	}

	/**
	 * Get title
	 * @return string
	 */
	final public function getTitle() {
		return $this->title;
	}

	/**
	 * Init Plot 
	 */
	private function initPlot() {
		$this->Plot = new Plot($this->getCSSid(), $this->width, $this->height);

		if (!$this->Training->hasArrayDistance() && !$this->Training->hasArrayTime()) {
			$this->Plot->hideXLabels();
		} elseif ($this->useStandardXaxis) {
			if ($this->Training->GpsData()->plotUsesTimeOnXAxis()) {
				$this->Plot->setXAxisAsTime();
				$this->Plot->setXAxisTimeFormat("%h:%M:%S");
				$this->Plot->Options['xaxis']['ticks'] = 5;
			} else
				$this->Plot->setXUnit('km');
		}

		if (!$this->showLegend)
			$this->Plot->hideLegend();

		$this->Plot->setTitle($this->title, 'right');
		$this->Plot->setTitle($this->Training->DataView()->getTitleForPlot(), 'left');
	}

	/**
	 * Set depending properties
	 * 
	 * Some properties need to know about the data.
	 * Therefore they have to be set after all other initializations.
	 */
	private function setDependingProperties() {
		if (!$this->Training->hasArrayDistance() && !$this->Training->hasArrayTime())
			return;

		if ($this->tracking)
			$this->Plot->enableTracking();
		if ($this->selecting)
			$this->Plot->enableSelection();
		if ($this->zooming)
			$this->Plot->enableZooming();
	}

	/**
	 * Output JS 
	 */
	final public function display() {
		echo Plot::getInnerDivFor($this->getCSSid(), $this->width, $this->height, false, 'training-chart');
		$this->Plot->outputJavaScript();
	}

	/**
	 * Get CSS id
	 * @return string
	 */
	final protected function getCSSid() {
		return $this->key.'_'.$this->Training->id();
	}

	/**
	 * Array average ignoring low values
	 * @param array $array
	 * @return int
	 */
	static public function averageWithoutLowValues(array $array) {
		$array = array_filter($array, 'TrainingPlot__ArrayFilterForLowEntries');

		if (empty($array))
			return 0;

		return round(array_sum($array) / count($array));
	}
}

/**
 * Filter-function: Remove all entries lower than 30 from array
 * @param mixed $value
 * @return boolean 
 */
function TrainingPlot__ArrayFilterForLowEntries($value) {
	return ($value > 30);
}