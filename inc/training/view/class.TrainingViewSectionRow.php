<?php
/**
 * This file contains class::TrainingViewSectionRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Context;

/**
 * Row of the training view
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionRow extends TrainingViewSectionRowAbstract {
	/**
	 * Plot
	 * @var \Runalyze\View\Activity\Plot\ActivityPlot
	 */
	protected $Plot = null;

	/**
	 * Boxed values
	 * @var BoxedValue[]
	 */
	protected $BoxedValues = array();

	/**
	 * Additional code
	 * @var string
	 */
	protected $Code = '';

	/**
	 * Additional content
	 * @var string
	 */
	protected $Header = '';

	/**
	 * Additional content
	 * @var string
	 */
	protected $Footer = '';

	/**
	 * With shadow?
	 * @var bool
	 */
	protected $withShadow = false;

	/**
	 * Disable scrolling and show full height
	 * @var bool
	 */
	protected $big = false;

	/**
	 * Constructor
	 */
	public function __construct(Context &$Context = null) {
		parent::__construct($Context);

		$this->setPlot();
	}

	/**
	 * Set plot
	 */
	abstract protected function setPlot();

	/**
	 * Display
	 */
	final public function display() {
		if (\Runalyze\Configuration::ActivityView()->showSectionsFullheight()) {
			$this->big = true;
			$this->withShadow = false;
		}

		echo '<div class="training-row">';

		if ($this->withShadow) {
			echo '<div class="training-row-info-shadow"></div>';
		}

		$this->displayInfo();
		$this->displayPlot();

		echo '</div>';
	}

	/**
	 * Display info
	 */
	protected function displayInfo() {
		$class = $this->big ? 'fullheight'
				: ($this->withShadow ? 'with-shadow' : '');

		echo '<div class="training-row-info '.$class.'">';

		if (!empty($this->BoxedValues)) {
			$this->displayBoxedValues();
		}

		if (!empty($this->Header)) {
			echo '<div class="panel-content">'.$this->Header.'</div>';
		}

		if (!empty($this->Code)) {
			echo '<div>' . $this->Code . '</div>';
		}

		if (!empty($this->Footer)) {
			echo '<div class="panel-content">'.$this->Footer.'</div>';
		}

		echo '</div>';
	}

	/**
	 * Display boxed values
	 */
	protected function displayBoxedValues() {
		$Code = '';

		foreach ($this->BoxedValues as &$Value) {
			$Code .= $Value->getCode();
		}

		BoxedValue::wrapValues($Code);
	}

	/**
	 * Display plot
	 */
	protected function displayPlot() {
		echo '<div class="training-row-plot">';

		if (!is_null($this->Plot)) {
			echo '<div id="plot-'.$this->Plot->getKey().'" class="plot-container">';
			$this->Plot->display();
			echo '</div>';
		}

		echo '</div>';
	}
}
