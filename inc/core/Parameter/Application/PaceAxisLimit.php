<?php
/**
 * This file contains class::PaceAxisLimit
 * @package Runalyze\System\Configuration\Value
 */

namespace Runalyze\Parameter\Application;

/**
 * Pace axis limit
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
abstract class PaceAxisLimit extends \Runalyze\Parameter\Select {
	/**
	 * Automatic
	 * @var string
	 */
	const AUTO = '0';

	/**
	 * Construct
	 * @param string $Key
	 */
	public function __construct() {
		parent::__construct(self::AUTO, array(
			'options'		=> array(
				0				=> __('automatic'),
				60				=> '1:00/km',
				120				=> '2:00/km',
				180				=> '3:00/km',
				240				=> '4:00/km',
				300				=> '5:00/km',
				360				=> '6:00/km',
				420				=> '7:00/km',
				480				=> '8:00/km',
				540				=> '9:00/km',
				600				=> '10:00/km',
				660				=> '11:00/km',
				720				=> '12:00/km',
				780				=> '13:00/km',
				840				=> '14:00/km',
				900				=> '15:00/km'
			)
		));
	}

	/**
	 * Is on automatic mode?
	 * @return bool
	 */
	final public function automatic() {
		return ($this->value() == self::AUTO);
	}
}
