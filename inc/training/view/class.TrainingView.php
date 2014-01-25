<?php
/**
 * This file contains class::TrainingView
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingView {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Training = null;

	/**
	 * Plot list
	 * @var TrainingPlotsList
	 */
	protected $PlotList = null;

	/**
	 * Checkable labels
	 * @var array
	 */
	protected $CheckableLabels = array();

	/**
	 * Toolbar links
	 * @var array
	 */
	protected $ToolbarLinks = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training Training object
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->initPlotList();
		$this->initToolbarLinks();
		$this->initCheckableLabels();
	}

	/**
	 * Init plot list
	 */
	private function initPlotList() {
		$this->PlotList = new TrainingPlotsList($this->Training);
	}

	/**
	 * Init toolbar links
	 */
	private function initToolbarLinks() {
		if (!Request::isOnSharedPage()) {
			$this->ToolbarLinks[] = Ajax::window('<a class="labeledLink" href="call/call.Training.edit.php?id='.$this->Training->id().'">'.Icon::$EDIT.' Bearbeiten</a> ','small');
			$this->ToolbarLinks[] = Ajax::window('<a class="labeledLink" href="'.ExporterWindow::$URL.'?id='.$this->Training->id().'">'.Icon::$DOWNLOAD.' Exportieren</a> ','small');
		}

		if ($this->Training->isPublic())
			$this->ToolbarLinks[] = SharedLinker::getToolbarLinkTo($this->Training->id());
	}

	/**
	 * Init checkable labels
	 */
	private function initCheckableLabels() {
		$this->CheckableLabels[] = array(
			'key'		=> 'details',
			'label'		=> 'Details',
			'show'		=> true,
			'visible'	=> CONF_TRAINING_SHOW_DETAILS
		);
		$this->CheckableLabels[] = array(
			'key'		=> 'zones',
			'label'		=> 'Zonen',
			'show'		=> ($this->Training->hasArrayPace()),
			'visible'	=> CONF_TRAINING_SHOW_ZONES
		);
		$this->CheckableLabels[] = array(
			'key'		=> 'rounds',
			'label'		=> 'Rundenzeiten',
			'show'		=> ($this->Training->hasArrayPace()),
			'visible'	=> CONF_TRAINING_SHOW_ROUNDS
		);
		$this->CheckableLabels[] = array(
			'key'		=> 'graphics',
			'label'		=> 'Karte &amp; Diagramme',
			'show'		=> (!$this->PlotList->isEmpty() || $this->Training->hasPositionData()),
			'visible'	=> CONF_TRAINING_SHOW_GRAPHICS
		);
	}

	/**
	 * Display
	 */
	public function display() {
		include FRONTEND_PATH.'training/tpl/tpl.Training.php';
	}

	/**
	 * Display title
	 */
	protected function displayTitle() {
		echo '<div class="icons-right panel-text-nav">';
		echo '<small>'.$this->Training->DataView()->getFullDateWithWeekLink().'</small>';
		echo '</div>';
		echo '<h1>'.$this->Training->DataView()->getTitleWithComment().'</h1>';

		if (!Request::isOnSharedPage())
			echo '<div class="hover-icons"><span class="link" onclick="Runalyze.reloadCurrentTab();">'.Icon::$REFRESH_SMALL.'</span></div>';
	}

	/**
	 * Display route on GoogleMaps
	 */
	public function displayRoute() {
		$Map = new Gmap($this->Training->id(), $this->Training->GpsData());
		$Map->displayMap();
	}

	/**
	 * Display training table
	 */
	public function displayTrainingTable() {
		$ViewTable = new TrainingViewTable($this->Training);
		$ViewTable->display();
	}

	/**
	 * Display training data
	 */
	public function displayTrainingData() {
		$this->displayRounds();
		$this->displayPaceZones();
		$this->displayPulseZones();
	}

	/**
	 * Display pace-zones
	 */
	public function displayPaceZones() {
		$Zones = new ZonesPace($this->Training);
		$Zones->display();
	}

	/**
	 * Display pace-zones
	 */
	public function displayPulseZones() {
		$Zones = new ZonesHeartrate($this->Training);
		$Zones->display();
	}

	/**
	 * Display surrounding container for rounds-data
	 */
	protected function displayRounds() {
		$RoundsView = new RoundsView($this->Training);
		$RoundsView->display();
	}
}