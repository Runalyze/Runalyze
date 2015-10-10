<?php
/**
 * This file contains class::VdotIcon
 * @package Runalyze\View\Icon
 */

namespace Runalyze\View\Icon;

use Runalyze\Configuration;

/**
 * VDOT icon
 * @author Hannes Christiansen
 * @package Runalyze\View\Icon
 */
class VdotIcon extends \Runalyze\View\Icon {
	/**
	 * Base layer: cloud
	 * @var string
	 */
	const BASE_CLASS = 'vdot-icon small';

	/**
	 * @var string
	 */
	const DIRECTION_UP = 'fa-arrow-up';

	/**
	 * @var string
	 */
	const DIRECTION_UP_HALF = 'fa-arrow-up  fa-rotate-45';

	/**
	 * @var string
	 */
	const DIRECTION_RIGHT = 'fa-arrow-right';

	/**
	 * @var string
	 */
	const DIRECTION_DOWN_HALF = 'fa-arrow-right  fa-rotate-45';

	/**
	 * @var string
	 */
	const DIRECTION_DOWN = 'fa-arrow-down';

	/**
	 * Transparent
	 * @var bool
	 */
	protected $IsTransparent = false;

	/**
	 * Direction
	 * @var string
	 */
	protected $Direction = '';

	/**
	 * Vdot Icon
	 * @param float $vdotValue
	 * @param float $currentShape
	 */
	public function __construct($vdotValue = null, $currentShape = null) {
		parent::__construct( self::BASE_CLASS );

		if (!is_null($vdotValue)) {
			$this->setDirectionBasedOn($vdotValue, $currentShape);
			$this->setTooltipFor($vdotValue);
		}
	}

	/**
	 * Set tooltip for
	 * @param float $vdotValue
	 */
	protected function setTooltipFor($vdotValue) {
		$this->setTooltip('VDOT: '.round($vdotValue, 2));
	}

	/**
	 * Set direction
	 * @param float $vdotValue
	 * @param float $currentShape
	 */
	protected function setDirectionBasedOn($vdotValue, $currentShape) {
		// TODO
		if (is_null($currentShape)) {
			$currentShape = Configuration::Data()->vdot();
		}

		$diff = $vdotValue - $currentShape;

		if ($diff > 3) {
			$this->setUp();
		} elseif ($diff > 1) {
			$this->setUpHalf();
		} elseif ($diff > -1) {
			$this->setRight();
		} elseif ($diff > -3) {
			$this->setDownHalf();
		} else {
			$this->setDown();
		}
	}

	/**
	 * Set transparent
	 */
	public function setTransparent() {
		$this->IsTransparent = true;
	}

	/**
	 * Set up
	 */
	public function setUp() {
		$this->Direction = self::DIRECTION_UP;
	}

	/**
	 * Set up half
	 */
	public function setUpHalf() {
		$this->Direction = self::DIRECTION_UP_HALF;
	}

	/**
	 * Set right
	 */
	public function setRight() {
		$this->Direction = self::DIRECTION_RIGHT;
	}

	/**
	 * Set down half
	 */
	public function setDownHalf() {
		$this->Direction = self::DIRECTION_DOWN_HALF;
	}

	/**
	 * Set down
	 */
	public function setDown() {
		$this->Direction = self::DIRECTION_DOWN;
	}

	/**
	 * Get code
	 * @return string
	 */
	public function code() {
		if ($this->IsTransparent) {
			$this->FontAwesomeName .= ' vdot-ignore';
		}

		$this->FontAwesomeName .= ' '.$this->Direction;

		return parent::code();
	}
}