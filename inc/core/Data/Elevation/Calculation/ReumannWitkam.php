<?php
/**
 * This file contains class::ReumannWitkamm
 * @package Runalyze\Data\Elevation\Calculation
 */

namespace Runalyze\Data\Elevation\Calculation;

/**
 * Smoothing strategy: Reumann-Witkamm
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Elevation\Calculation
 * @see http://psimpl.sourceforge.net/reumann-witkam.html
 */
class ReumannWitkam extends Strategy {
	/**
	 * Smooth data
	 */
	public function runSmoothing() {
		throw new \RuntimeException('Not implemented.');
	}
}