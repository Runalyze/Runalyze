<?php
/**
 * This file contains class::TrainingViewSection
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section of the training view
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSection {
	/**
	 * Header
	 * @var string
	 */
	protected $Header = '';

	/**
	 * All rows
	 * @var SectionRow[]
	 */
	protected $Rows = array();

	/**
	 * Is first?
	 * @var bool
	 */
	protected $isFirst = false;

	/**
	 * Training
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Constructor
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		if ($this->hasRequiredData())
			$this->setHeaderAndRows();
	}

	/**
	 * Set header and rows
	 */
	abstract protected function setHeaderAndRows();

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	abstract protected function hasRequiredData();

	/**
	 * Set header
	 * @param string $header
	 */
	final protected function setHeader($header) {
		$this->Header = $header;
	}

	/**
	 * Append row
	 * @param TrainingViewSectionRowAbstract $Row
	 */
	final protected function appendRow(TrainingViewSectionRowAbstract &$Row) {
		$this->Rows[] = $Row;
	}

	/**
	 * Set as first
	 */
	final public function setAsFirst() {
		$this->isFirst = true;
	}

	/**
	 * Is this section empty?
	 * @return bool
	 */
	final public function isEmpty() {
		return empty($this->Header) || empty($this->Rows);
	}

	/**
	 * Display
	 */
	public function display() {
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
		echo '<h2>'.$this->Header.'</h2>';
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
}