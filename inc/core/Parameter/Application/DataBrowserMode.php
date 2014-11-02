<?php
/**
 * This file contains class::DataBrowserMode
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Data browser mode
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class DataBrowserMode extends \Runalyze\Parameter\Select {
	/**
	 * Month
	 * @var string
	 */
	const WEEK = 'week';

	/**
	 * Week
	 * @var string
	 */
	const MONTH = 'month';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::WEEK, array(
			'options'		=> array(
				self::WEEK		=> __('Week view'),
				self::MONTH		=> __('Month view')
			)
		));
	}

	/**
	 * Show week
	 * @return bool
	 */
	public function showWeek() {
		return ($this->value() == self::WEEK);
	}

	/**
	 * Show month
	 * @return bool
	 */
	public function showMonth() {
		return ($this->value() == self::MONTH);
	}
}