<?php
/**
 * This file contains class::TrainingViewSectionRowTabbedPlot
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Plot\ActivityPlot;

/**
 * Row of the training view
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionRowTabbedPlot extends TrainingViewSectionRow {
	/**
	 * Right content
	 * @var array
	 */
	protected $RightContent = array();

	/**
	 * Right content title
	 * @var array
	 */
	protected $RightContentTitle = array();


    protected $TopContent="";

	/**
	 * CSS id
	 * @var string
	 */
	protected $cssID = '';

	/**
	 * Constructor
	 */
	public function __construct(Context &$Context = null) {
		$this->Context = $Context;

		$this->setContent();
		$this->setRightContent();
	}

	/**
	 * Set CSS id
	 * @param string $cssID
	 */
	final public function setCSSid($cssID) {
		$this->cssID = $cssID;
	}

	/**
	 * Set plot
	 */
	final protected function setPlot() {}

	/**
	 * Set right content
	 */
	abstract protected function setRightContent();

	/**
	 * Add right content
	 * @param string $key
	 * @param string $title
	 * @param string $content
	 */
	final protected function addRightContent($key, $title, $content) {
		$this->RightContent[$key] = $content;
		$this->RightContentTitle[$key] = $title;
	}

	/**
	 * Get links
	 * @return array
	 */
	final public function getLinks() {
		return $this->RightContentTitle;
	}

	/**
	 * Display plot
	 */
	protected function displayPlot() {
		echo '<div id="training-view-tabbed-'.$this->cssID.'" class="training-row-plot">';

		$first = true;

        echo $this->TopContent;

		foreach ($this->RightContent as $key => $Content) {
			echo '<div class="change" id="training-view-tabbed-'.$this->cssID.'-'.$key.'"'.(!$first ? ' style="display:none;"' : '').'>';

			if ($Content instanceof ActivityPlot) {
				echo '<div id="plot-'.$Content->getKey().'" class="plot-container">';
				$Content->display();
				echo '</div>';
			} else {
				echo $Content;
			}

			echo '</div>';

			$first = false;
		}

		echo '</div>';
	}
}