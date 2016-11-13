<?php
/**
 * This file contains class::TableLapsAbstract
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Context;

/**
 * Table for laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TableLapsAbstract {
	/**
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/**
	 * @var \Runalyze\Data\Laps\Laps
	 */
	protected $Laps;

	/**
	 * Code
	 * @var string
	 */
	protected $Code = '';

	/**
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context) {
		$this->Context = $context;

		$this->setCode();
	}

	/**
	 * Set code
	 */
	abstract protected function setCode();

	/**
	 * Get code
	 * @return string
	 */
	final public function getCode() {
		return $this->Code;
	}
}
