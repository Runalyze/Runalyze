<?php
/**
 * This file contains class::ElevationServer
 * @package Runalyze\Parameter\Application
 */
/**
 * Elevation server
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class ElevationServer extends ParameterSelect {
	/**
	 * Geonames
	 * @var string
	 */
	const GEONAMES = 'geonames';

	/**
	 * Google
	 * @var string
	 */
	const GOOGLE = 'google';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::GEONAMES, array(
			'options'		=> array(
				self::GEONAMES	=> 'ws.geonames.org',
				self::GOOGLE	=> 'maps.googleapis.com'
			)
		));
	}

	/**
	 * Uses: Google
	 * @return bool
	 */
	public function usesGoogle() {
		return ($this->value() == self::GOOGLE);
	}

	/**
	 * Uses: Genoames
	 * @return bool
	 */
	public function usesGeonames() {
		return ($this->value() == self::GEONAMES);
	}
}