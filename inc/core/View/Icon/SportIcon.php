<?php
/**
 * This file contains class::SportIcon
 * @package Runalyze\View\Icon
 */

namespace Runalyze\View\Icon;

/**
 * Sport icon
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon
 */
class SportIcon extends \Runalyze\View\Icon {
	/**
	 * Filename
	 * @var string
	 */
	protected $Filename;

	/**
	 * Sport icon
	 * @param string $filename
	 */
	public function __construct($filename) {
		parent::__construct('');

		$this->Filename = $filename;
	}

	/**
	 * Display
	 */
	public function code() {
		return '<i class="'.$this->Filename.'"'.$this->tooltipAttributes().'></i>';
	}
}
