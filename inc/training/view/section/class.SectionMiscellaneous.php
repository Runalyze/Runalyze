<?php
/**
 * This file contains class::SectionMiscellaneous
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Context;

/**
 * Section: Miscellaneous
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionMiscellaneous extends TrainingViewSectionTabbedPlot {
	/**
	 * @var bool
	 */
	protected $showCadence;

	/**
	 * Constructor
	 */
	public function __construct(Context &$Context = null, $showCadence = true) {
		$this->showCadence = $showCadence;

		parent::__construct($Context);
	}

	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Miscellaneous');

		$this->appendRowTabbedPlot( new SectionMiscellaneousRow($this->Context, $this->showCadence) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return true;
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'misc';
	}
}