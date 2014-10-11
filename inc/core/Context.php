<?php
/**
 * This file contains class::Context
 * @package Runalyze
 */

namespace Runalyze;

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
	static private $Objects = array();

	/**
	 * Athlete
	 * @return Athlete
	 */
	static public function Athlete() {
		if (!isset(self::$Objects['athlete'])) {
			self::$Objects['athlete'] = new Athlete(
				\Configuration::General()->gender(),
				\Configuration::Data()->HRmax(),
				\Configuration::Data()->HRrest()
			);
		}

		return self::$Objects['athlete'];
	}
}