<?php
/**
 * This file contains class::DatabaseOrder
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * DatabaseOrder
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class DatabaseOrder extends \Runalyze\Parameter\Select {
	/**
	 * ID: ascending
	 * @var string
	 */
	const ASC = 'id-asc';

	/**
	 * ID: descending
	 * @var string
	 */
	const DESC = 'id-desc';

	/**
	 * Alphabetical
	 * @var string
	 */
	const ALPHA = 'alpha';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::ASC, array(
			'options'		=> array(
				self::ASC		=> __('id (oldest first)'),
				self::DESC		=> __('id (latest first)'),
				self::ALPHA		=> __('alphabetical')
			)
		));
	}

	/**
	 * As mysql query-string
	 * @return string
	 */
	public function asQuery() {
		switch ($this->value()) {
			case self::ALPHA:
				return 'ORDER BY `name` ASC';
			case self::DESC:
				return 'ORDER BY `id` DESC';
			case self::ASC:
			default:
				return 'ORDER BY `id` ASC';
		}
	}
}