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
	 * Sections
	 * @var TrainingViewSection[]
	 */
	protected $Sections = array();

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

		$this->initToolbarLinks();
		$this->initSections();
	}

	/**
	 * Init toolbar links
	 */
	private function initToolbarLinks() {
		if ($this->Training->isPublic())
			$this->ToolbarLinks[] = SharedLinker::getToolbarLinkTo($this->Training->id());

		if (!Request::isOnSharedPage()) {
			$this->ToolbarLinks[] = Ajax::window('<a href="'.ExporterWindow::$URL.'?id='.$this->Training->id().'">'.Icon::$DOWNLOAD.' '.__('Export').'</a> ','small');
			$this->ToolbarLinks[] = Ajax::window('<a href="call/call.Training.edit.php?id='.$this->Training->id().'">'.Icon::$EDIT.' '.__('Edit').'</a> ','small');
		}

		$this->ToolbarLinks[] = $this->Training->DataView()->getDateLinkForMenu();
	}

	/**
	 * Init sections
	 */
	protected function initSections() {
		$this->Sections[] = new SectionOverview($this->Training);
		$this->Sections[] = new SectionLaps($this->Training);
		$this->Sections[] = new SectionHeartrate($this->Training);
		$this->Sections[] = new SectionPace($this->Training);
		$this->Sections[] = new SectionRoute($this->Training);
		$this->Sections[] = new SectionMiscellaneous($this->Training);
	}

	/**
	 * Display
	 */
	public function display() {
		$this->displayHeader();
		$this->displaySections();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo '<div class="panel-heading">';

		if (!Request::isOnSharedPage())
			$this->displayHeaderMenu();

		echo '<h1>'.$this->Training->DataView()->getTitleWithComment().'</h1>';

		if (!Request::isOnSharedPage())
			$this->displayReloadLink();

		echo '</div>';
	}

	/**
	 * Display header menu
	 */
	protected function displayHeaderMenu() {
		echo '<div class="panel-menu"><ul>';

		foreach ($this->ToolbarLinks as $Link)
			echo '<li>'.$Link.'</li>';

		echo '</ul></div>';
	}

	/**
	 * Display reload link
	 */
	protected function displayReloadLink() {
		echo '<div class="hover-icons"><span class="link" onclick="Runalyze.reloadCurrentTab();">'.Icon::$REFRESH_SMALL.'</span></div>';
	}

	/**
	 * Display sections
	 */
	protected function displaySections() {
		foreach ($this->Sections as &$Section)
			$Section->display();

		$this->initPlots();
	}

	/**
	 * Init plots
	 */
	protected function initPlots() {
		echo Ajax::wrapJSforDocumentReady( 'RunalyzePlot.resizeTrainingCharts();' );
	}
}