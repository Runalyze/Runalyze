<?php
/**
 * This file contains class::Icon
 * @package Runalyze\View
 */

namespace Runalyze\View;

use Runalyze\View\Tooltip;

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
	 * Key to identfiy FontAwesome icon
	 * @see http://fontawesome.io/icons/
	 * @var string
	 */
	protected $fontAwesomeName;

	/**
	 * Text for tooltip
	 * @var string
	 */
	protected $tooltip;

	/**
	 * Construct new icon
	 * @param string $fontAwesomeName [optional] class const
	 */
	public function __construct($fontAwesomeName) {
		$this->fontAwesomeName = $fontAwesomeName;
	}

	/**
	 * Set tooltip
	 * @param string $tooltip
	 */
	public function setTooltip($tooltip) {
		$this->tooltip = $tooltip;
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
		return '<i class="fa fa-fw '.$this->fontAwesomeName.'"'.$this->tooltipAttributes().'></i>';
	}

	/**
	 * Tooltip attributes
	 * @return string
	 */
	protected function tooltipAttributes() {
		if (!empty($this->tooltip)) {
			$Tooltip = new Tooltip($this->tooltip);
			return ' '.$Tooltip->attributes();
		}

		return '';
	}

	/**
	 * Add tooltip to code
	 * @param string $code
	 */
	protected function addTooltipToCode(&$code) {
		if (!empty($this->tooltip)) {
			$Tooltip = new Tooltip($this->tooltip);
			$Tooltip->wrapAround($code);
		}
	}
}