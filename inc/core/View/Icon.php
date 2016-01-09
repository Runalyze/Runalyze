<?php
/**
 * This file contains class::Icon
 * @package Runalyze\View
 */

namespace Runalyze\View;

/**
 * Icon
 * 
 * Example usage:
 * <pre>
 * $Icon = new Icon( Icon::INFO );
 * $Icon->setTooltip('That\'s so cool!');
 * $Icon->display();
 * </pre>
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View
 */
class Icon {
	/**
	 * @var string
	 */
	const INFO = 'fa-info-circle';

	/**
	 * @var string
	 */
	const CLOCK = 'fa-clock-o';

	/**
	 * @var string
	 */
	const CLOCK_GRAY = 'fa-clock-o unimportant';

	/**
	 * @var string
	 */
	const HEART = 'fa-heart';

	/**
	 * @var string
	 */
	const MAP_ARROW = 'fa-location-arrow';

	/**
	 * Key to identfiy FontAwesome icon
	 * @see http://fontawesome.io/icons/
	 * @var string
	 */
	protected $FontAwesomeName;

	/**
	 * Text for tooltip
	 * @var string
	 */
	protected $Tooltip;

	/**
	 * Construct new icon
	 * @param string $fontAwesomeName [optional] class const
	 */
	public function __construct($fontAwesomeName) {
		$this->FontAwesomeName = $fontAwesomeName;
	}

	/**
	 * Set tooltip
	 * @param string $tooltip
	 */
	public function setTooltip($tooltip) {
		$this->Tooltip = $tooltip;
	}

	/**
	 * Display
	 */
	final public function display() {
		echo $this->code();
	}

	/**
	 * Get code
	 * @return string
	 */
	public function code() {
		return '<i class="fa fa-fw '.$this->FontAwesomeName.'"'.$this->tooltipAttributes().'></i>';
	}

	/**
	 * Tooltip attributes
	 * @return string
	 */
	protected function tooltipAttributes() {
		if (!empty($this->Tooltip)) {
			$Tooltip = new Tooltip($this->Tooltip);
			return ' '.$Tooltip->attributes();
		}

		return '';
	}

	/**
	 * Add tooltip to code
	 * @param string $code
	 */
	protected function addTooltipToCode(&$code) {
		if (!empty($this->Tooltip)) {
			$Tooltip = new Tooltip($this->Tooltip);
			$Tooltip->wrapAround($code);
		}
	}
}