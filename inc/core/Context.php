<?php
/**
 * This file contains class::Context
 * @package Runalyze
 */

namespace Runalyze;

use Runalyze\Configuration;

use SessionAccountHandler;

/**
 * Context
 * 
 * Currently a static container of widely used objects.
 * 
 * This static container is used over a real dependency injection container
 * or service locator due to missing code completion and due to the fact that
 * Runalyze is not using hundreds of these nearly global dependencies.
 * 
 * @author Hannes Christiansen
 * @package Runalyze
 */
class Context {
	/**
	 * Objects
	 * @var array
	 */
	private static $Objects = array();

	/**
	 * Athlete
	 * @return \Runalyze\Athlete
	 */
	public static function Athlete() {
		if (!isset(self::$Objects['athlete'])) {
			self::$Objects['athlete'] = new Athlete(
				Configuration::General()->gender(),
				Configuration::Data()->HRmax(),
				Configuration::Data()->HRrest(),
				null,
				null,
				Configuration::Data()->vdot()
			);
		}

		return self::$Objects['athlete'];
	}

	/**
	 * Athlete
	 * @return \Runalyze\Model\Factory
	 */
	public static function Factory() {
		if (!isset(self::$Objects['factory'])) {
			self::$Objects['factory'] = new Model\Factory(
				SessionAccountHandler::getId()
			);
		}

		return self::$Objects['factory'];
	}

	/**
	 * Clear internal cache
	 */
	public static function reset() {
		self::$Objects = array();
	}
}