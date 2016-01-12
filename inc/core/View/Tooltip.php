<?php
/**
 * This file contains class::Tooltip
 * @package Runalyze\View
 */

namespace Runalyze\View;

/**
 * Tooltip
 * 
 * Example usage:
 * <pre>
 * $Tooltip = new Tooltip( 'I\'m a tooltip!' );
 * $Tooltip->setPosition( Tooltip::POSITION_LEFT );
 * $Tooltip->wrapAround( $code );
 * </pre>
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View
 */
class Tooltip {
	/**
	 * @var string
	 */
	const POSITION_LEFT = 'atLeft';

	/**
	 * @var string
	 */
	const POSITION_RIGHT = 'atRight';

	/**
	 * @var string
	 */
	const ATTRIBUTE_REL = 'tooltip';

	/**
	 * Attribute to be used for the content
	 * @var string
	 */
	const ATTRIBUTE = 'title';

	/**
	 * Text for tooltip
	 * @var string
	 */
	protected $text;

	/**
	 * Position
	 * @var string
	 */
	protected $position = '';

	/**
	 * Construct new tooltip
	 * @param string $text
	 */
	public function __construct($text) {
		$this->text = $text;
	}

	/**
	 * Set text
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
	}

	/**
	 * Set position
	 * @param string $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * Wrap around
	 * @param string $code
	 */
	public function wrapAround(&$code) {
		$code = '<span '.$this->attributes().'>'.$code.'</span>';
	}

	/**
	 * Get attributes
	 * @return string
	 */
	public function attributes() {
		$Attributes = array();
		$Attributes[] = 'rel="'.self::ATTRIBUTE_REL.'"';
		$Attributes[] = self::ATTRIBUTE.'="'.str_replace('&amp;nbsp;', '&nbsp;', htmlspecialchars($this->text)).'"';

		if (!empty($this->position)) {
			$Attributes[] = 'class="'.$this->position.'"';
		}

		return implode(' ', $Attributes);
	}
}