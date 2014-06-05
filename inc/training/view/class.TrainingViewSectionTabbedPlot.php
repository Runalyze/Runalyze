<?php
/**
 * This file contains class::TrainingViewSectionTabbedPlot
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section of the training view
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionTabbedPlot extends TrainingViewSection {
	/**
	 * All menus
	 * @var array
	 */
	protected $Links = array();

	/**
	 * Append row
	 * @param TrainingViewSectionRowTabbedPlot $Row
	 */
	final protected function appendRowTabbedPlot(TrainingViewSectionRowTabbedPlot &$Row) {
		$this->Rows[] = $Row;
		$this->Links += $Row->getLinks();

		$Row->setCSSid( $this->cssId() );
	}

	/**
	 * Display
	 */
	final public function display() {
		if (!$this->isEmpty()) {
			$this->displayHeader();
			$this->displayContent();
		}
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo '<div class="panel-heading'.(!$this->isFirst ? ' panel-inner-heading' : '').'">';
		$this->displayChangeMenu();
		echo '<h2>'.$this->Header.'</h2>';
		echo '</div>';
	}

	/**
	 * Display change menu
	 */
	private function displayChangeMenu() {
		if (count($this->Links) <= 1)
			return;

		echo '<div class="change-menu">';

		foreach ($this->Links as $key => $Title)
			echo Ajax::change($Title, 'training-view-tabbed-'.$this->cssId(), 'training-view-tabbed-'.$this->cssId().'-'.$key);

		echo '</div>';
	}

	/**
	 * Display content
	 */
	private function displayContent() {
		echo '<div>';

		foreach ($this->Rows as $Row)
			$Row->display();

		echo '</div>';
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	abstract protected function cssId();
}