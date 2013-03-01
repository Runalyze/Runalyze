<?php
/**
 * Class: TrainingPlot
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class TrainingPlot {
	/**
	 * Training object
	 * @var Training
	 */
	protected $Training = null;

	/**
	 * Training ID
	 * @var int
	 */
	protected $TrainingID = 0;

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
	 * @param Training $Training
	 */
	public function __construct(Training &$Training) {
		$this->Training   = $Training;
		$this->TrainingID = $Training->id();

		$this->setKeyAndTitle();
		$this->initPlot();
		$this->initData();
		$this->setProperties();
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

		if ($this->useStandardXaxis) {
			if ($this->Training->GpsData()->plotUsesTimeOnXAxis()) {
				$this->Plot->setXAxisAsTime();
				$this->Plot->setXAxisTimeFormat("%h:%M:%S");
				$this->Plot->Options['xaxis']['ticks'] = 5;
			} else
				$this->Plot->setXUnit('km');
		}

		if ($this->tracking)
			$this->Plot->enableTracking();
		if ($this->selecting)
			$this->Plot->enableSelection();
		if ($this->zooming)
			$this->Plot->enableZooming();

		if (!$this->showLegend)
			$this->Plot->hideLegend();

		$this->Plot->setTitle($this->title, 'right');
		$this->Plot->setTitle($this->Training->getPlotTitle(), 'left');
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
		return $this->key.'_'.$this->TrainingID;
	}
}