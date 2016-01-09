<?php
/**
 * This file contains class::TrainingViewSectionTabbed
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section of the training view
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionTabbed extends TrainingViewSection {
	/**
	 * All tabbed rows
	 * @var TrainingViewSectionRowAbstract[]
	 */
	protected $RowsTabbed = array();

	/**
	 * All tabbed rows: Title
	 * @var string[]
	 */
	protected $RowsTabbedTitle = array();

	/**
	 * Append row
	 * @param TrainingViewSectionRowAbstract $Row
	 * @param string $Title
	 */
	final protected function appendRowTabbed(TrainingViewSectionRowAbstract &$Row, $Title) {
		$this->RowsTabbed[] = $Row;
		$this->RowsTabbedTitle[] = $Title;
	}

	/**
	 * Display
	 */
	final public function display() {
		if (!empty($this->Header) && (!empty($this->RowsTabbed) || !empty($this->Rows))) {
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
		if (count($this->RowsTabbedTitle) <= 1)
			return;

		echo '<div class="change-menu">';

		foreach ($this->RowsTabbedTitle as $i => $Title)
			echo Ajax::change($Title, 'training-view-tabbed-'.$this->cssId(), 'training-view-tabbed-'.$this->cssId().'-'.$i);
 
		echo '</div>';
	}

	/**
	 * Display content
	 */
	private function displayContent() {
		echo '<div id="training-view-tabbed-'.$this->cssId().'">';

		foreach ($this->RowsTabbed as $i => $Row) {
			echo '<div class="change" id="training-view-tabbed-'.$this->cssId().'-'.$i.'"'.($i != 0 ? ' style="display:none;"' : '').'>';
			$Row->display();
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	abstract protected function cssId();
}